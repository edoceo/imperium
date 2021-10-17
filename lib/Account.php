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
		'Asset' => 'Asset',
		'Asset: Bank' => 'Asset: Bank',
		'Asset: Accounts Receivable' => 'Asset: Accounts Receivable',
		'Asset: Inventory' => 'Asset: Inventory (Current Asset)',
		'Asset: Fixed' => 'Asset: Fixed',
		'Asset: Other' => 'Asset: Other',
		'Liability' => 'Liability',
		'Liability: Accounts Payable' => 'Liability: Accounts Payable',
		'Liability: Credit Card' => 'Liability: Credit Card',
		'Liability: Long Term' => 'Liability: Long Term',
		'Liability: Other' => 'Liability: Other',
		'Revenue' => 'Revenue',
		'Expense' => 'Expense',
		'Equity' => 'Equity',
		'Equity: Owners Capital' => 'Equity: Owners Capital',
		'Equity: Owners Drawing' => 'Equity: Owners Drawing',
		'Sub' => 'Sub Ledgers',
		'Sub: Client' => 'Client Ledgers',
		'Sub: Vendor' => 'Vendor Ledgers',
	);

	/**
		Account Delete
	*/
	function delete()
	{
		// Remove Ledger Entries
		$sql = 'DELETE FROM account_ledger WHERE account_journal_id IN (SELECT account_journal_id FROM account_ledger WHERE account_id = ?)';
		$arg = array($this->_data['id']);
		SQL::query($sql, $arg);

		// And the Journal Entries
		$sql = 'DELETE FROM account_journal WHERE id NOT IN (SELECT account_journal_id FROM account_ledger)';
		SQL::query($sql);

		// And th Account
		$sql = 'DELETE FROM account WHERE id = ?';
		$arg = array($this->_data['id']);
		SQL::query($sql, $arg);

		return true;
	}

	/**
	*/
	function save()
	{
		if (empty($this->_data['code'])) {
			$this->_data['code'] = 0;
		}
		if (intval($this->_data['parent_id'])==0) $this->_data['parent_id'] = null;
		if (intval($this->_data['account_tax_line_id'])==0) $this->_data['account_tax_line_id'] = null;
		if (floatval($this->_data['balance'])==0) $this->_data['balance'] = 0;
		if (intval($this->_data['link_to'])==0) $this->_data['link_to'] = null;
		if (intval($this->_data['link_id'])==0) $this->_data['link_id'] = null;

		// Build the Parent Path and Full Name
		$code_path = array();
		if (!empty($this->_data['code'])) {
			$code_path[] = $this->_data['code'];
		}
		$parent_id = $this->_data['parent_id'];
		$i = 0;

		// Multiple Parent Tree?
		// Not Really Supported
		while ($parent_id) {
			$i++;
			$sql = 'SELECT parent_id,code FROM account where id = ?';
			$arg = array($parent_id);
			$res = SQL::fetch_row($sql, $arg);
			if ($res) {
				$parent_id = $res['parent_id'];
				if (!empty($res['code'])) {
					$code_path[] = $res['code'];
				}
			}
			$parent_id = null;
			if ($i > 5) {
				break;
			}
		}

		$this->_data['active'] = 't';
		if (count($code_path) > 0) {
			$this->_data['full_code'] = implode('/',array_reverse($code_path));
		}
		if (!empty($this->_data['full_code'])) {
			$this->_data['full_name'] = $this->_data['full_code'] . ' - ';
		}
		$this->_data['full_name'].= $this->_data['kind'] . ':' . $this->_data['name'];

		/*
		$k = strtok($this->_data['kind'], ':');
		switch ($k) {
			case 'Asset':
				$this->_data['kind_sort'] = 10;
				break;
			case 'Expense':
				$this->_data['kind_sort'] = 30;
				break;
			default:
				$this->_data['kind_sort'] = 300;
		}
		*/

		$ret = parent::save();

		$this->balanceUpdate();

	}

	/**
		Update the Balance of the Account to the Current Value
	*/
	function balanceUpdate()
	{
		// Get Current Balance
		$x = SQL::fetch_one('SELECT sum(amount) AS balance_update FROM account_ledger WHERE account_id = ?', array($this->_data['id']));
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
	function balanceAt($date=null, $ex_close=false)
	{
		// @todo Detect the Period
		// @todo Detect Account Type - Permanant Accounts since life of Biz, Temp since previous period

		$sql = 'SELECT sum(amount) AS balance_at FROM general_ledger WHERE account_id = ?';
		$arg = array($this->_data['id']);

		if (!empty($date)) {
			$sql.= ' AND account_ledger_date <= ? ';
			$arg[] = $date;
		}

		if ($ex_close) {
			$sql.= ' AND kind != ? ';
			$arg[] = 'C';
		}

		$ret = SQL::fetch_one($sql, $arg);

		// Correct Balance to Positive Number
		if ($this->_isDebitSide()) {
			$ret = $ret * -1;
		}

		return floatval($ret);
	}

	/**
		Account balanceBefore displays the balance before given date
	*/
	function balanceBefore($date, $ex_close=false)
	{
		$sql = 'SELECT sum(amount) AS balance FROM general_ledger';
		$sql.= ' WHERE account_id = ? AND account_ledger_date < ?';
		$arg = array(
			$this->_data['id'],
			$date,
		);
		if ($ex_close) {
			$sql.= ' AND kind != \'C\'';
		}
		$x = SQL::fetch_one($sql,$arg);

		// Correct Balance to Positive Number
		if ($this->_isDebitSide()) {
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
		$sql = 'SELECT sum(amount) AS balance_span FROM general_ledger ';
		$sql.= ' WHERE account_id = ?';
		$arg[] = intval($this->_data['id']);
		$sql.= ' AND (account_ledger_date >= ? AND account_ledger_date <= ? )';
		$arg[] = $date_alpha;
		$arg[] = $date_omega;

		if ($ex_close) {
			$sql.= ' AND kind != ?';
			$arg[] = 'C';
		}

		$ret = SQL::fetch_one($sql, $arg);
		if ($this->_isDebitSide()) {
		  $ret = $ret * -1;
		}

		return floatval($ret);
	}

	/**

	*/
	function creditTotal($a_ts, $z_ts, $with_closing=true)
	{
		$sql = "SELECT sum(amount) AS credit_sum FROM general_ledger ";
		$sql.= ' WHERE account_id = ? ';
		$sql.= ' AND (account_ledger_date >= ? and account_ledger_date <= ? ) ';
		$sql.= ' AND amount > 0 ';
		$arg = array(
			$this->_data['id'],
			$a_ts,
			$z_ts
		);
		$x = SQL::fetch_one($sql, $arg);
		return abs($x);
	}

	/**

	*/
	function debitTotal($a_ts, $z_ts, $with_closing=true)
	{
		$sql = "SELECT sum(amount) AS debit_sum from general_ledger ";
		$sql.= ' WHERE account_id = ?';
		$sql.= ' AND (account_ledger_date >= ? and account_ledger_date <= ? )';
		$sql.= ' AND amount < 0';
		$arg = array(
			$this->_data['id'],
			$a_ts,
			$z_ts
		);
		$x = SQL::fetch_one($sql, $arg);
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

	/**
		If it's a Debit Side Bias Account (Asset, Expense, Drawing, etc)
	*/
	private function _isDebitSide()
	{
		$k = $this->_data['kind'];

		if ( (substr($k,0,5)=='Asset') || (substr($k,0,7)=='Expense') || (strpos($k,'Drawing') > 0) ) {
			return true;
		}

		return false;

	}
}
