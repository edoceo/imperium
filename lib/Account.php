<?php
/**
	Account Class

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class Account extends ImperiumBase
{
	protected $_table = 'account';

	//const OBJECT_TYPE = 100;

	// These seem to be unused
	// Base Types
	const ASSET	 = 0x00010000;
	const LIABILITY = 0x00020000;
	const EQUITY	= 0x00040000;
	const REVENUE   = 0x00100000;
	const EXPENSE   = 0x00200000;
	// Life Times
	const PERMANENT = 0x00001000;
	const TEMPORARY = 0x00002000;
	// Sub Type
	const CASH	  = 0x00000100;
	const AP		= 0x00000200;
	const AR		= 0x00000400;
	// External Link
	const CHECKING  = 0x00000010;
	const SAVINGS   = 0x00000020;
	const MARKET	= 0x00000040;

	/**
		Imperium Variables
	*/
	public static $kind_list = array(
		'Asset'=>'Asset',
		'Asset: Bank'=>'Asset: Bank',
		'Asset: Accounts Receivable'=>'Asset: Accounts Receivable',
		'Asset: Inventory'=>'Asset: Inventory (Current Asset)',
		'Asset: Fixed'=>'Asset: Fixed',
		'Asset: Other'=>'Asset: Other',
		'Liability'=>'Liability',
		'Liability: Accounts Payable'=>'Liability: Accounts Payable',
		'Liability: Credit Card'=>'Liability: Credit Card',
		'Liability: Long Term'=>'Liability: Long Term',
		'Liability: Other'=>'Liability: Other',
		'Revenue'=>'Revenue',
		'Revenue: Invoices'=>'Revenue: Invoices',
		'Revenue: Misc'=>'Revenue: Misc',
		'Expense'=>'Expense',
		'Expense: Cost of Goods Sold'=>'Expense: Cost of Goods Sold',
		'Expense: Misc'=>'Expense: Misc',
		'Equity'=>'Equity',
		'Equity: Owners Capital'=>'Equity: Owners Capital',
		'Equity: Owners Drawing'=>'Equity: Owners Drawing',
		'Sub' => 'Sub Ledgers',
		'Sub: Customer' => 'Customer Ledgers',
		'Sub: Vendor' => 'Vendor Ledgers',
		'Sub: Asset' => 'Fixed Asset Ledgers',
	);

	/**
		Account Delete
	*/
	function delete()
	{
		$id = intval($this->id);
		$db = Zend_Registry::get('db');

		// Remove Ledger Entries
		$db->query("delete from account_ledger where account_journal_id in (select account_journal_id from account_ledger where account_id = $id)");
		// And the Journal Entries
		$db->query("delete from account_journal where id not in (select account_journal_id from account_ledger)");
		// And th Account
		$db->query("delete from account where id = $id");

		return true;
	}

	/**
	*/
	function save()
	{
		if (intval($this->_data['parent_id'])==0) $this->_data['parent_id'] = null;
		if (intval($this->_data['account_tax_line_id'])==0) $this->_data['account_tax_line_id'] = null;
		if (floatval($this->_data['balance'])==0) $this->_data['balance'] = null;
		if (intval($this->_data['link_to'])==0) $this->_data['link_to'] = null;
		if (intval($this->_data['link_id'])==0) $this->_data['link_id'] = null;

		// Build the Parent Path and Full Name
		$path = array();
		$path[] = $this->_data['code'];
		$parent_id = $this->_data['parent_id'];
		$i = 0;
		while ($parent_id) {
			$i++;
			$rs = SQL::fetchRow('SELECT parent_id,code FROM account where id = ?', array($parent_id));
			if ($rs) {
				$parent_id = $rs['parent_id'];
				$path[] = $rs['code'];
			}
			$parent_id = null;
			if ($i > 5) {
				break;
			}
		}
		$this->_data['active'] = 't';
		$this->_data['full_code'] = implode('/',array_reverse($path));
		$this->_data['full_name'] = $this->_data['full_code'] . ' - ' . $this->_data['kind'] . ':' . $this->_data['name'];
		$ret = parent::save();
		$this->balanceUpdate();
		//$this->balanceAtEnd(date('Y-m-d 23:59'));
	}

	/**
		@deprecated
	*/
//	function __get($key)
//	{
//		return false;
//		die("@deprecated __get($key)");
//		switch ($key) {
//		/*
//		case 'code_full':
//				$code[] = $this->code;
//				$parent_id = $this->parent_id;
//				while ($parent_id)
//				{
//					$acct = $idc->fetchRow("select parent_id,code from account where id=$parent_id");
//					if ($acct)
//					{
//						$parent_id = $acct->parent_id;
//						$code[] = $acct->code;
//					}
//					if (count($code) > 5) break;
//				}
//				return implode('/', array_reverse($code) );
//		*/
//	case 'is_parent':
//	  $rs = $db->fetchOne("select count(id) from account where parent_id=$this->id");
//	  return $rs->c > 0;
//	case 'life':
//	  return $this->flag & (Account::PERMANENT | Account::TEMPORARY);
//	case 'type':
//	  // todo: this is broken
//	  return $this->flag & (Account::ASSET | Account::LIABILITY | Account::EQUITY | Account::REVENUE | Account::EXPENSE);
//	case 'type_code':
//	  if ($this->flag & Account::ASSET) return 'A';
//	  elseif ($this->flag & Account::LIABILITY) return 'L';
//	  elseif ($this->flag & Account::EQUITY) return 'E';
//	  elseif ($this->flag & Account::REVENUE) return 'R';
//	  elseif ($this->flag & Account::EXPENSE) return 'X';
//	  else return 'Unknown';
//	case 'type_name':
//	  if ($this->flag & Account::ASSET) return 'Asset';
//	  elseif ($this->flag & Account::LIABILITY) return 'Liability';
//	  elseif ($this->flag & Account::EQUITY) return 'Equity';
//	  elseif ($this->flag & Account::REVENUE) return 'Revenue';
//	  elseif ($this->flag & Account::EXPENSE) return 'Expense';
//	  else return 'Unknown';
//	case 'childAccountList':
//			$db = Zend_Registry::get('db');
//	  $rs = $db->fetchAll("select * from account where parent_id=$this->id order by flag,code,name");
//	  return $rs;
//	case 'flag_as_string':
//	  $ret = '';
//	  // Life
//	  if ($this->flag & Account::TEMPORARY) $ret.= 'Temporary,';
//	  if ($this->flag & Account::PERMANENT) $ret.= 'Permanent,';
//	  // Type
//	  if ($this->flag & Account::ASSET) $ret.= 'Asset,';
//	  if ($this->flag & Account::LIABILITY) $ret.= 'Liability,';
//	  if ($this->flag & Account::EQUITY) $ret.= 'Equity,';
//	  if ($this->flag & Account::REVENUE) $ret.= 'Revenue,';
//	  if ($this->flag & Account::EXPENSE) $ret.= 'Expense,';
//	  /// Sub Type
//	  if ($this->flag & Account::CASH) $ret.= 'Cash,';
//	  if ($this->flag & Account::AP) $ret.= 'Accounts Payable,';
//	  if ($this->flag & Account::AR) $ret.= 'Accounts Receiveable,';
//	  // Class
//	  if ($this->flag & Account::CHECKING) $ret.= 'Checking,';
//	  if ($this->flag & Account::SAVINGS) $ret.= 'Savings,';
//	  if ($this->flag & Account::MARKET) $ret.= 'Market,';
//	  return strlen($ret) ? substr($ret,0,-1) : null;
//	case 'flag_hex':
//			return dechex($this->flag);
//		}
//	return null;
//	}

/**
		Update the Balance of the Account to the Current Value
	*/
	function balanceUpdate()
	{
		// Get Current Balance
		$x = SQL::fetch_one('SELECT sum(amount) FROM account_ledger WHERE account_id = ?', array($this->_data['id']));
		$balance = floatval($x);

		// Sum the Child Accounts
		// $rs = $this->child_accounts;
		// foreach ($rs as $a) {
		//	 $x = $db->fetchOne("select sum(amount) from account_ledger where account_id=$a->id");
		//	 $balance += floatval($x);
		// }

		// Update Account
		SQL::query("UPDATE account SET balance = ? WHERE id = ?", array($balance, $this->_data['id']));
		return $balance;
	}

	static function account_get_next_code($type)
	{


	}

	/**
		Account balanceAt balance at close of a day
	*/
	function balanceAt($date,$ex_close=false)
	{
		// @todo Detect the Period
		// @todo Detect Account Type - Permanant Accounts since life of Biz, Temp since previous period

		//$sql = "select sum(amount) as balance from general_ledger ";
		//$sql.= " where account_id=$this->id and date < '$date' and date>='2006-01-01'";

		$sql = 'SELECT sum(amount) FROM general_ledger WHERE account_id = ? AND date <= ? ';
		$arg = array($this->_data['id'],$date);

		if ($ex_close) {
			$sql.= ' AND kind != ? ';
			$arg[] = 'C';
		}

		$ret = SQL::fetch_one($sql, $arg);
		// Correct Balance to Positive Number
		if ( (substr($this->_data['kind'],0,5)=='Asset') || (substr($this->_data['kind'],0,7)=='Expense') || (strpos($this->_data['kind'],'Drawing') > 0) ) {
			$ret = $ret * -1;
		}
		return floatval($ret);
	}

	/**
		Account balanceBefre displays the balance before given date
	*/
	function balanceBefore($date,$ex_close=false)
	{
		// @todo Detect the Period
		// @todo Detect Account Type - Permanant Accounts since life of Biz, Temp since previous period
		//$sql = "select sum(amount) as balance from general_ledger ";
		//$sql.= " where account_id=$this->id and date < '$date' and date>='2006-01-01'";
	
		// $sql = $d->select();
		// $sql->from('general_ledger',array('sum(amount) as balance'));
		// $sql->where('account_id = ?',intval($this->id));
		// //$sql->where('date >= ?',$date_alpha);
		// $sql->where('date < ?',$date);
		// if ($ex_close) {
		//	 $sql->where('kind != ?','C');
		//	 //$sql.= " and kind != 'C' ";
		// }
		// // echo $sql->assemble() . '<br />';
		// $x = $d->fetchOne($sql);
		
		$sql = 'SELECT sum(amount) AS balance FROM general_ledger WHERE account_id = ? AND date < ?';
		$arg = array(
			$this->id,
			$date,
		);
		if ($ex_close) $sql.= ' AND kind != \'C\'';
		$x = SQL::fetch_one($sql,$arg);
		
		// Correct Balance to Positive Number
		if ( (substr($this->kind,0,5)=='Asset') || (substr($this->kind,0,7)=='Expense') || (strpos($this->kind,'Drawing') > 0) ) {
			$x = $x * -1;
		}
		return floatval($x);
	}

	/**
		Account balanceSpan() displays the balance change between two dates
		@todo Detect the Period
	*/
	function balanceSpan($date_alpha,$date_omega,$ex_close=false)
	{
		$arg = array();
		$sql = 'SELECT sum(amount) FROM general_ledger ';
		$sql.= ' WHERE account_id = ?';
		$arg[] = intval($this->_data['id']);
		$sql.= ' AND (date >= ? AND date <= ? )';
		$arg[] = $date_alpha;
		$arg[] = $date_omega;

		if ($ex_close) {
			$sql.= ' AND kind != ?';
			$arg[] = 'C';
		}

		$ret = SQL::fetch_one($sql, $arg);
		$k = $this->_data['kind'];
		if ( (substr($k,0,5)=='Asset') || (substr($k,0,7)=='Expense') || (strpos($k,'Drawing') > 0) ) {
		  $ret = $ret * -1;
		}

		return floatval($ret);
	}

	/**
	*/
	function creditTotal($a_ts,$z_ts,$with_closing=true)
	{
		$sql = "select sum(amount) from general_ledger ";
		$sql.= " where account_id=$this->id ";
		$sql.= " and (date >= '$a_ts' and date <= '$z_ts' ) ";
		$sql.= " and amount > 0 ";
		$x = SQL::fetch_one($sql);
		return abs($x);
	}
  /**
  */
	function debitTotal($a_ts,$z_ts,$with_closing=true)
	{
		$sql = "select sum(amount) from general_ledger ";
		$sql.= " where account_id=$this->id and ";
		$sql.= "  (date >= '$a_ts' and date <= '$z_ts' ) ";
		$sql.= " and amount < 0 ";
		$x = SQL::fetch_one($sql);
		return abs($x);
	}

	/**
		Account findPeriod($date)
		Returns the Period ID for a Specific Date
	
		@param $date is the date to find the period for
		@param $fmt is the return format id|range|
	*/
	// static function findPeriod($date,$return='id')
	// {
	//	 // @todo z_date and a_date should be date_alpha and date_omega
	//	 $db = Zend_Registry::get('db');
	//	 $sql = "select id,a_date,z_date,status_id from account_period where a_date <= '$date' and z_date >= '$date'";
	//	 $rs = $db->fetchRow($sql);
	//	 if ($rs) {
	//		 switch ($fmt) {
	//		 case 'id':
	//			 return $rs->id;
	//		 case 'range':
	//			 return array($rs->a_date,$rs->z_date);
	//		 case 'object':
	//			 return $rs;
	//		 }
	//	 }
	//	 return null;
	// }

	/**
		Account listAccounts() static functions
		Returns a list of the Accounts as an array of stdClass() objects
	*/
	static function listAccounts()
	{
		$sql = "SELECT account.*, account_tax_line.line || ': ' || account_tax_line.name AS account_tax_line_name ";
		$sql.= ' FROM account ';
			$sql.= ' LEFT JOIN account_tax_line ON account.account_tax_line_id = account_tax_line.id';
			// $sql.= ' JOIN account_tax_form ON account_tax_line.account_tax_form_id = account_tax_form.id ';
		$sql.= ' ORDER BY full_code ASC, code ASC';

		$rs = SQL::fetch_all($sql);
		$list = array();
		foreach ($rs as $x) {
			$list[] = new Account($x);
		}
		return $list;
	}

	/**
		Account listAccounts() static functions
		Returns a list of the Accounts as an array of stdClass() objects
	*/
	static function listAccountPairs()
	{
		$sql = "select id,full_name ";
		$sql.= " from account ";
		$sql.= " order by full_code asc, code asc";

		// $rs = $db->fetchPairs($sql);
		$rs = SQL::fetch_mix($sql);
		return $rs;
	}
}
