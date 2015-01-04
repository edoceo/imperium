<?php
/**
	@file
	@brief Invoice Model

	A Bill to Send to a Contact

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  2003
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class Invoice extends ImperiumBase
{
	const FLAG_OPEN = 0x00000001;
	// const FLAG_POST = 0x00001000;
	const FLAG_SENT = 0x00040000;
	const FLAG_HAWK = 0x00080000;
	const FLAG_VOID = 0x00400000;
	const FLAG_PAID = 0x00800000;

	protected $_table = 'invoice';

	/**
		ImperiumBase findByHash
	*/
	static function findByHash($h)
	{
		$sql = "select id from invoice where hash = ?";
		$id = SQL::fetch_one($sql,array($h));
		if ($id) {
		  $x = new Invoice($id);
		  return $x;
		}
		return null;
	}

	/**
		Create an Invoice
	*/
	function __construct($x=null)
	{

		$this->_data['status'] = $_ENV['invoice']['status'];
		$this->_data['net'] = $_ENV['invoice']['term_days'];
		$this->_data['date'] = date('Y-m-d');
		$this->_data['bill_amount'] = 0;
		$this->_data['paid_amount'] = 0;
		$this->_data['sub_total'] = 0;
		$this->_data['tax_total'] = 0;
		$this->_data['bill_address_id'] = null;
		$this->_data['ship_address_id'] = null;

		parent::__construct($x);

		// Now to Stuff with this new Data!
		// @todo Update Properties
		// @todo Update due_diff to the number of days until or after payment is due
		$due_date = strtotime($this->_data['date']);
		if (!empty($this->_data['net'])) {
			$due_date += ($this->_data['net'] * 86400); // add($this->net,Zend_Date::DAY);
		}
		$now_date = time();
		$this->_date['due_diff'] = floor(($now_date - $due_date) / 86400);
	}

	/**
		Invoice Model save()
	*/
	function save()
	{
		if (empty($this->_data['auth_user_id'])) {
			$this->_data['auth_user_id'] = $_SESSION['uid'];
		}

		parent::save();
		$this->_updateBalance();
	}

	/**
	*/
	function delete()
	{
		$id = intval($this->_data['id']);

		SQL::query('DELETE FROM base_note WHERE link = ?', array($this->link()));
		SQL::query('DELETE FROM invoice_item WHERE invoice_id = ?', array($id));
		SQL::query('DELETE FROM invoice WHERE id = ?', array($id));

		return true;
	}

	/**
		Determines if this Invoice is Hawk-able
		@return true|false
	*/
	function canHawk()
	{
		if ($this->_data['status'] == 'Paid') {
			return false;
		}

		if ($this->hasFlag(self::FLAG_PAID))  {
			return false;
		}

		return !$this->hasFlag(self::FLAG_HAWK);
	}

	/**
		Imperium Specific Functions
	*/
	function addInvoiceItem($ivi)
	{
		$ivi['auth_user_id'] = $this->_data['auth_user_id'];
		$ivi['invoice_id'] = $this->_data['id'];
		$ivi->save();
		// Base_Diff::note($this,'Invoice Item: ' . $r['name'] . ' created');
		$this->_updateBalance();
	}

	/**
	*/
	function delInvoiceItem($id)
	{
		// Base_Diff::note($this,'Invoice Item #' . $id . ' removed');
		SQL::query('DELETE FROM invoice_item WHERE id = ?', array($id));
		$this->_updateBalance();
		return true;
	}

	/**
		@return ResultSet of InvoiceItems
	*/
	function getInvoiceItems()
	{
		$sql = 'SELECT * FROM invoice_item WHERE invoice_id = ? ORDER BY line, rate DESC, quantity DESC';
		$arg = array($this->_data['id']);
		$res = SQL::fetch_all($sql, $arg);

		$ret = array();
		foreach ($res as $x) {
			$ret[] = new InvoiceItem($x);
		}
		return $ret;
	}

	/**
		Invoice::getWorkOrders
		Returns a list of WorkOrders that have contributed to this Invoice
	*/
	function getWorkOrders()
	{
		if (intval($this->id)==0) {
		  return null;
		}
		$db = Zend_Registry::get('db');
		$sql = 'select distinct b.* from workorder_item_invoice_item a';
		$sql.= ' join workorder b on a.workorder_id = b.id';
		$sql.= ' where a.invoice_id = ' . $this->id;
		$x = $db->fetchAll($sql);
		return $x;
	}

	// Return the set of LedgerEntry objects that match
	function getTransactions()
	{
		// Transaction
		if (intval($this->id)==0) {
			return null;
		}

		$sql = 'SELECT al.id,al.account_id,al.amount,aj.id as account_journal_id,aj.date,aj.note,a.name as account_name ';
		$sql.= ' from account_ledger al ';
		$sql.= ' join account_journal aj on al.account_journal_id = aj.id ';
		$sql.= ' join account a on al.account_id = a.id ';
		// KIND needs to be A/R + Asset //
		$sql.= ' WHERE ';
		// $sql.= " a.kind = 'Asset: Accounts Receivable' AND ";
		$sql.= sprintf(' al.link_to = %d AND al.link_id = %d',self::getObjectType($this),$this->id);
		$sql.= ' ORDER BY aj.date ASC, al.amount DESC';

		$res = SQL::fetch_all($sql);
		return $res;
	}

	/**
		Sum of Transactions
	*/
	function getTransactionSum()
	{
		$id = intval($this->_data['id']);
		if ($id <= 0) {
			return null;
		}

		$sql = "SELECT abs(sum(al.amount)) from account_ledger al ";
		$sql.= " JOIN account_journal aj on al.account_journal_id = aj.id ";
		$sql.= " JOIN account a on al.account_id = a.id ";
		// KIND needs to be A/R + Asset //
		$sql.= " WHERE al.link_to=" . self::getObjectType($this) . " and al.link_id=$id";
		// If Posting & Paying do this
		$sql.= ' AND amount < 0 ';
		// Elseif CASH basis don't use AND amount...
		$ret = SQL::fetch_one($sql);
		return $ret;
	}

	/**
		Update Balance
		Updates the Invoice Balance after it's been saved
	*/
	private function _updateBalance()
	{
		$id = intval($this->_data['id']);
		
		$sql = 'UPDATE invoice SET';
		$sql.= ' sub_total = ( SELECT SUM ( quantity * rate * (1 + tax_rate)) FROM invoice_item WHERE invoice_id = ?)';;
		$sql.= ', tax_total = ( SELECT SUM ( quantity * rate * tax_rate) FROM invoice_item WHERE invoice_id = ?)';
		$sql.= ', bill_amount = ( SELECT SUM ( quantity * rate * (1 + tax_rate)) FROM invoice_item WHERE invoice_id = ?) ';
		// $sql.= ', paid_amount =
		$sql.= ' WHERE id = ? ';
		SQL::query($sql, array($id, $id, $id, $id));
		// die(SQL::lastError());

		// $r = array();
		// $r['sub_total'] = floatval($d->fetchOne("select sum( quantity * rate ) as sub_total from invoice_item where invoice_id={$id}"));
		// $r['tax_total'] = floatval($d->fetchOne("select sum( quantity * rate * tax_rate) as tax_total from invoice_item where invoice_id={$id}"));
		// $sql.= ' sub_total = ?, ';
		// $arg[] = floatval(SQL::fetch_one("select sum( quantity * rate * tax_rate) as tax_total from invoice_item where invoice_id={$id}"));
		// // $r['bill_amount'] = $r['sub_total'] + $r['tax_total'];
		// $sql.= ' bill_amount = ?, ';
		// $arg[] = $r['sub_total'] + $r['tax_total'];
		// // $r['paid_amount'] = $this->getTransactionSum();
		// $sql.= ' paid_amount = ? ';
		// $arg[] = $this->getTransactionSum();

		// @todo Force Marking as Paid Amount Full?
		// if ($this->status == 'Paid') {
		//	 $r['paid_amount'] = $r['bill_amount'];
		// }
		// $w = array('id = ?'=>$this->id);
		// $t = new Zend_Db_Table(array('name'=>'invoice'));
		// $t->update($r,$w);

		// @todo Save to Object Data?
		// $this->bill_amount = $r['bill_amount'];
		// $this->paid_amount = $r['paid_amount'];
		// $this->sub_total = $r['sub_total'];
		// $this->tax_total = $r['tax_total'];

	}
}
