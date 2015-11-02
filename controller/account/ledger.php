<?php
/**
	@file
	@brief View an Account Ledger
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

$order = null;
$param = array();
$where = null;

// Load Specified Account or Session Account
if ( ($id = intval($_GET['id'])) > 0) {
	$this->Account = new Account($id);
} elseif ( ($id = intval($_GET['id'])) > 0) {
	$this->Account = new Account($id);
} elseif ( ($id = intval($_GET['id'])) == -1) {
	$this->Account = new Account(-1);
} elseif (!empty($_SESSION['account-id'])) {
	$this->Account = new Account($_SESSION['account-id']);
} else {
	$this->Account = new Account();
}

if ( (strtolower($_GET['c'])=='post') && (!empty($this->Account['id'])) ) {

	// Post to this Account
	// New Transaction Holder
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry['note'] = null;
	$at->AccountLedgerEntryList = array();

	// First Item is this Account
	$a = new Account( $this->Account['id'] );
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	// $ale->amount = abs($Invoice->bill_amount) * -1;
	// $ale->link_to = ImperiumBase::getObjectType($Invoice);
	// $ale->link_id = $Invoice->id;
	$at->AccountLedgerEntryList[] = $ale;
	// Next Line Accounts Receivable
	$ale = new AccountLedgerEntry();
	$at->AccountLedgerEntryList[] = $ale;

	$_SESSION['account-transaction'] = $at;
	$_SESSION['return-path'] = sprintf('/account/ledger?id=%d',$this->Account['id']);
	$this->redirect('/account/transaction');
}

if (empty($this->Account['id'])) {

	// Show General Ledger (All Accounts!)
	$this->openBalance = 0;

	$where = " (date>='{$this->date_alpha}' and date<='{$this->date_omega}') ";
	$order = " date,kind, account_journal_id, amount asc ";

	$this->dr_total = SQL::fetch_one("select sum(amount) from general_ledger where amount < 0 and $where");
	$this->cr_total = SQL::fetch_one("select sum(amount) from general_ledger where amount > 0 and $where");

	$this->Account = new Account(array('name'=>'General Ledger'));

} else {

	// Show this specific Account
	$_SESSION['account-id'] = $this->Account['id'];

	$this->openBalance = $this->Account->balanceBefore($this->date_alpha);

	$where = " (account_id = ? OR parent_id = ?) AND (date >= ? AND date <= ?) ";
	$param = array(
		$this->Account['id'],
		$this->Account['id'],
		$this->date_alpha,
		$this->date_omega,
	);
	$order = " date,kind desc,amount asc ";

	//$this->AccountLedger = $data;
	$this->dr_total = abs($this->Account->debitTotal($this->date_alpha,$this->date_omega));
	$this->cr_total = abs($this->Account->creditTotal($this->date_alpha,$this->date_omega));
}

if (strlen($_GET['link'])) {
	// $l = ImperiumBase::getObjectType($o)
	$l = Base_Link::load($_GET['link']);
	$link_to = Base_Link::getObjectType($l,'id');  // Get Object Type ID
	$link_id = $l->id;
	if ( (!empty($link_to)) && (!empty($link_id)) ) {
		$where .= sprintf(' and link_to = %d and link_id = %d ',$link_to,$link_id);
	}
}

$sql = "SELECT * FROM general_ledger WHERE $where ORDER BY $order";
$res = SQL::fetch_all($sql, $param);

$this->LedgerEntryList = $res;

$this->openBalance = $this->Account->balanceAt($this->date_alpha);

// ImperiumView::mruAdd($this->link(),'Ledger ' . $this->Account->name);
$_SESSION['return-path'] = '/account/ledger';

