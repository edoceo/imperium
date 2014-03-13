<?php
/**
	@file
	@brief Show and Save Transactions
*/

// View!
$id = intval($_GET['id']);
if ($id < 0) {
	unset($_SESSION['account-transaction']);
	unset($_SESSION['account-transaction-list']);
	unset($_GET['id']);
	$id = null;
}

$_ENV['title'] = array('Accounts','Transaction', $id ? "#$id" : 'New' );

if (!empty($_POST['a'])) $_POST['a'] = strtolower($_POST['a']);

switch ($_POST['a']) {
case 'save':
case 'save-copy':

	$id = intval($_GET['id']);

	$_SESSION['account-transaction'] = null;
	$_SESSION['account-transaction-list'] = array();

	// Delete
	// if ($req->getPost('c') == 'Delete') {
	// 	$aje = new AccountJournalEntry($id);
	// 	$aje->delete();
	// 	$this->_s->info = 'Journal Entry #' . $id . ' deleted';
	// 	$this->redirect('/account/ledger');
	// }

	// $this->_d->beginTransaction();

	$aje = new AccountJournalEntry($id);
	$aje['auth_user_id'] = $_SESSION['uid'];
	$aje['date'] = $_POST['date'];
	$aje['note'] = $_POST['note'];
	$aje['kind'] = $_POST['kind'];
	$aje->save();
	$_SESSION['account-transaction'] = $aje;

	// $this->_s->AccountJournalEntry->date = $this->_request->getPost('date');
	// Was throwing and __PHP_INcomplete_Class error ?
	// $_SESSION['account']['date'] = $_POST['date'];

	// And Make the Wizard
	// $awj = AccountWizardJournal::makeFromAccountJournal($aje);

	if ($id) {
		radix_session::flash('info', 'Account Journal Entry #' . $id . ' updated');
	} else {
		radix_session::flash('info', 'Account Journal Entry #' . $aje['id'] . ' created');
	}

	// Save Ledger Entries
	foreach ($_POST as $k=>$v) {
		// Trigger process only when matchin this
		if (!preg_match('/^(\d+)_id$/',$k,$m)) {
			continue; // ignore others
		}

		$i = $m[1];

		// Debit or Credit
		$dr = floatval( preg_replace('/[^\d\.]+/',null,$_POST["{$i}_dr"]));
		$cr = floatval( preg_replace('/[^\d\.]+/',null,$_POST["{$i}_cr"]));
		// Skip Empty
		if ( ($cr == 0) && ($dr == 0) ) {
			continue;
		}

		$id = intval($_POST["{$i}_id"]);
		$ale = new AccountLedgerEntry($id);
		$ale['auth_user_id'] = $_SESSION['uid'];
		$ale['account_id'] = $_POST["{$i}_account_id"];
		$ale['account_journal_id'] = $aje['id'];
		// $ale->note = $req->getPost($i . '_note');
		$ale['amount'] = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
		// Bind to an object
		$ale['link_id'] = $_POST["{$i}_link_id"];
		$ale['link_to'] = $_POST["{$i}_link_to"];
		// Save Ledger Entry
		$ale->save();

		// $_SESSION['account-transaction-list'][] = $ale;
		// Save Ledger Entry to Wizard
		// $awj->addLedgerEntry($ale);

		if ($id) {
			radix_session::flash('info', 'Account Ledger Entry #' . $id . ' updated');
		} else {
			radix_session::flash('info', 'Account Ledger Entry #' . $ale['id'] . ' created');
		}
	}

	// Memorise the Transaction
	if (1 == $_POST['memorise']) {
		// $awj->save();
		radix_session::flash('info', 'Account Wizard Memorised');
	}

	// File!
	if ( (!empty($_FILES['file'])) && (Base_File::goodPost($_FILES['file'])) ) {
		 $bf = Base_File::copyPost($_FILES['file']);
		 $bf['link'] = $bf->link($aje);
		 $bf->save();
		 radix_session::flash('info', 'Attachment Created');
	}

	// Commit and Redirect
	// $this->_d->commit();

	// if ('Apply' == $_POST['c']) {
	// 	$this->_redirect('/account/transaction?id=' . $aje->id);
	// }

	if ('save-copy' == $_POST['a']) {
		// $_SESSION['account-transaction'] = $aje;
		// $_SESSION['account-transaction-list' = array();
		// ] = $ale;
		// // $_SESSION['new-transaction'] = $aje;
		radix::redirect('/account/transaction');
	}

	// Redirect Back
	$ret = '/account/ledger';
	if (!empty($_SESSION['return-path'])) {
		$ret = $_SESSION['return-path'];
		unset($_SESSION['return-path']);
	}
	radix::redirect($ret);
	break;
}

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
// } elseif (isset($this->_s->AccountTransaction)) {
} elseif (!empty($_SESSION['account-transaction'])) {
	die('no-account-sessoin');
	$this->AccountJournalEntry = $_SESSION['account-transaction']['aje']; // $this->_s->AccountTransaction->AccountJournalEntry;
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
if (empty($this->AccountJournalEntry['date'])) {
	$this->AccountJournalEntry['date'] = isset($_SESSION['account']['date']) ? $_SESSION['account']['date'] : date('Y-m-d');
}

// Add Prev / Next Links
$this->jump_list = array();
if (!empty($this->AccountJournalEntry['id'])) {

	// Prev Five
	$s = sprintf('SELECT id FROM account_journal where id < %d order by id desc limit 5',$this->AccountJournalEntry['id']);
	$r = radix_db_sql::fetch_all($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
	// This
	$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$this->AccountJournalEntry['id']);
	// Next Five
	$s = sprintf('SELECT id FROM account_journal where id > %d order by id asc limit 5',$this->AccountJournalEntry['id']);
	$r = radix_db_sql::fetch_all($s);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
}

$this->LinkToList = array(
	'' => null,
	'contact' => 'Contact',
	'invoice' => 'Invoice',
	'workorder' => 'Work Order',
);
