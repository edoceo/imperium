<?php

// $rq = $this->getRequest();

// Load Specified Account or Session Account
if ( ($id = intval($_GET['id'])) > 0) {
	$this->Account = new Account($id);
} elseif ( ($id = intval($_GET['id'])) > 0) {
	$this->Account = new Account($id);
} elseif ( ($id = intval($_GET['id'])) == -1) {
	$this->Account = new Account(-1);
} elseif ($this->_s->Account) {
	$this->Account = new Account($this->_s->Account->id);
} else {
	$this->Account = new Account();
}

if ( (strtolower($_GET['c'])=='post') && (!empty($this->Account->id)) ) {

	// Post to this Account
	// New Transaction Holder
	$at = new stdClass();
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry->note = null;
	$at->AccountLedgerEntryList = array();

	// First Item is this Account
	$a = new Account( $this->Account->id );
	$ale = new AccountLedgerEntry();
	$ale->account_id = $a->id;
	$ale->account_name = $a->full_name;
	// $ale->amount = abs($Invoice->bill_amount) * -1;
	// $ale->link_to = ImperiumBase::getObjectType($Invoice);
	// $ale->link_id = $Invoice->id;
	$at->AccountLedgerEntryList[] = $ale;
	// Next Line Accounts Receivable
	$ale = new AccountLedgerEntry();
	$at->AccountLedgerEntryList[] = $ale;

	$this->_s->AccountTransaction = $at;
	$this->_s->ReturnTo = sprintf('/account/ledger?id=%d',$this->Account->id);
	$this->redirect('/account/transaction');
}

if (empty($this->Account->id)) {

	// Show General Ledger (All Accounts!)
	unset($this->_s->Account);
	$this->openBalance = 0;

	$where = " (date>='{$this->date_alpha}' and date<='{$this->date_omega}') ";
	$order = " date,kind, account_journal_id, amount asc ";

	$this->dr_total = radix_db_sql::fetch_one("select sum(amount) from general_ledger where amount < 0 and $where");
	$this->cr_total = radix_db_sql::fetch_one("select sum(amount) from general_ledger where amount > 0 and $where");

	$this->Account = new Account(array('name'=>'General Ledger'));
} else {
	// Show this specific Account
	$_SESSION['account'] = $this->Account;
	$this->openBalance = $this->Account->balanceBefore($this->date_alpha);

	$where = " (account_id={$this->Account->id} OR parent_id = {$this->Account->id}) and (date>='{$this->date_alpha}' and date<='{$this->date_omega}') ";
	// $where.= " and amount < 0 ";
	$order = " date,kind desc,amount asc ";

	$this->title = array('Ledger',"{$this->Account->full_name} from {$this->date_alpha_f} to {$this->date_omega_f}");
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

$sql = "select * from general_ledger where $where order by $order";
$this->LedgerEntryList = radix_db_sql::fetchAll($sql);

$_ENV['title'] = array('General Ledger',"{$this->date_alpha_f} to {$this->date_omega_f}");
// ImperiumView::mruAdd($this->link(),'Ledger ' . $this->Account->name);
// $this->_s->ReturnTo = '/account/ledger';
$_SESSION['return-path'] = '/account/ledger';

