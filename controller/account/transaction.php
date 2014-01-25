<?php

// $req = $this->getRequest();
// if ($req->isPost()) {
// 	$this->transactionActionPost();
// }

// View!
$id = intval($_GET['id']);
$_ENV['title'] = array('Accounts','Transaction', $id ? "#$id" : 'Create' );

if ($id) {
	$this->AccountJournalEntry = new AccountJournalEntry($id); //
	//$this->_d->fetchRow("select * from account_journal where id = $id");
	//$sql = $this->_d->select();
	//$sql->from('general_ledger');
	//$sql->where('account_journal_id = ?', $id);
	//$sql->order(array('account_full_code','amount'));
	
	// $sql = $this->_d->select();
	// $sql->from(array('al'=>'account_ledger'));
	// $sql->join(array('a'=>'account'),'al.account_id = a.id',array('a.full_name as account_name'));
	// $sql->where('al.account_journal_id = ?', $id);
	// //$sql->order(array('al.amount asc','a.full_code'));
	// $sql->order(array('al.id asc'));
	// // $sql = "select * from account_ledger where account_journal_id=$id order by amount"
	
	$sql = 'SELECT account_ledger.*, account.full_name as account_name';
	$sql.= ' FROM account_ledger';
	$sql.= ' JOIN account ON account_ledger.account_id = account.id';
	$sql.= ' WHERE account_ledger.account_journal_id = ? ';
	$sql.= ' ORDER BY account_ledger.amount ASC, account.full_code ';

	$this->AccountLedgerEntryList = radix_db_sql::fetch_all($sql, array($id)); // $this->_d->fetchAll($sql);
	$this->FileList = $this->AccountJournalEntry->getFiles();
} elseif (isset($this->_s->AccountTransaction)) {
	$this->AccountJournalEntry = $this->_s->AccountTransaction->AccountJournalEntry;
	$this->AccountLedgerEntryList = $this->_s->AccountTransaction->AccountLedgerEntryList;
	// @todo Here on on Save (above)?
	unset($this->_s->AccountTransaction);
} else {
	$this->AccountJournalEntry = new AccountJournalEntry(null);
	$this->AccountLedgerEntryList = array();
	$this->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
	$this->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
}

// Correct Missing Date
if (empty($this->AccountJournalEntry->date)) {
	$this->AccountJournalEntry->date = isset($this->_s->AccountJournalEntry->date) ? $this->_s->AccountJournalEntry->date : date('Y-m-d');
}

// Add Prev / Next Links
$this->jump_list = array();
if (!empty($this->AccountJournalEntry->id)) {

	// Prev Five
	$s = sprintf('SELECT id FROM account_journal where id < %d order by id desc limit 5',$this->AccountJournalEntry->id);
	$r = radix_db_sql::fetch_all($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
	// This
	$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$this->AccountJournalEntry->id);
	// Next Five
	$s = sprintf('SELECT id FROM account_journal where id > %d order by id asc limit 5',$this->AccountJournalEntry->id);
	$r = radix_db_sql::fetch_all($s);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
}

$this->LinkToList = array(
	null=>null,
	ImperiumBase::getObjectType('contact')   =>'Contact',
	ImperiumBase::getObjectType('invoice')   =>'Invoice',
	ImperiumBase::getObjectType('workorder') =>'Work Order',
);
