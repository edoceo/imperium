<?php
/**
	Show and Save Transactions
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

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
case 'delete':

	$aje = new AccountJournalEntry(intval($_GET['id']));
	$adp = AccountPeriod::findByDate($aje['date']);
	if ($adp->isClosed()) {
		//Session::flash('fail', 'Account Period is closed');
		//Radix::redirect();
	}

	$aje->delete();
	Session::flash('info', sprintf('Journal Entry %d deleted', $aje['id']));

	// Redirect
	$ret = '/account';
	if (!empty($_GET['r']) && ('/' == substr($_GET['r'], 0, 1))) {
		$ret = $_GET['r'];
	}

	Radix::redirect($ret);

	break;

case 'save':
case 'save-copy':

	$adp = AccountPeriod::findByDate($_POST['date']);
	if ($adp->isClosed()) {
		Session::flash('fail', 'Account Period is closed');
		Radix::redirect();
	}

	$id = intval($_GET['id']);

	$_SESSION['account-transaction'] = null;
	$_SESSION['account-transaction-list'] = array();

	if (empty($aje['id'])) {
		$_SESSION['account']['date'] = $_POST['date'];
	}

	// $this->_d->beginTransaction();

	$aje = new AccountJournalEntry($id);
	$aje['auth_user_id'] = $_SESSION['uid'];
	$aje['date'] = $_POST['date'];
	$aje['note'] = $_POST['note'];
	$aje['kind'] = $_POST['kind'];

	$aje['flag'] = 0;
	if (!empty($_POST['flag']) && (is_array($_POST['flag']))) {
		foreach ($_POST['flag'] as $i => $f) {
			$aje->setFlag($f);
			//echo "\$aje->setFlag($f);\n";
		}
	}

	$aje->save();

	$_SESSION['account-transaction'] = $aje;

	// And Make the Wizard
	// $awj = AccountWizardJournal::makeFromAccountJournal($aje);

	if ($id) {
		Session::flash('info', 'Account Journal Entry #' . $id . ' updated');
	} else {
		Session::flash('info', 'Account Journal Entry #' . $aje['id'] . ' created');
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

		$id = intval($_POST["{$i}_id"]);
		$ale = new AccountLedgerEntry($id);
		$ale['auth_user_id'] = $_SESSION['uid'];
		$ale['account_id'] = intval($_POST["{$i}_account_id"]);
		$ale['account_journal_id'] = $aje['id'];
		$ale['amount'] = ($dr > $cr) ? abs($dr) * -1 : abs($cr);

		// Skip Empty
		if ( ($ale['account_id'] == 0) && ($ale['amount'] == 0) ) {
			continue;
		}

		// Bind to an object
		//$ale['link'] = sprintf('%s:%d', $_POST["{$i}_link_to"], $_POST["{$i}_link_id"]);
		// Save Ledger Entry
		$ale->save();

		// $_SESSION['account-transaction-list'][] = $ale;
		// Save Ledger Entry to Wizard
		// $awj->addLedgerEntry($ale);

		if ($id) {
			Session::flash('info', 'Account Ledger Entry #' . $id . ' updated');
		} else {
			Session::flash('info', 'Account Ledger Entry #' . $ale['id'] . ' created');
		}
	}

	// Memorise the Transaction
	if (1 == $_POST['memorise']) {
		// $awj->save();
		Session::flash('info', 'Account Wizard Memorised');
	}

	// File!
	if ( (!empty($_FILES['file'])) && (Base_File::goodPost($_FILES['file'])) ) {
		 $bf = Base_File::copyPost($_FILES['file']);
		 $bf['link'] = $bf->link($aje);
		 $bf->save();
		 Session::flash('info', 'Attachment Created');
	}

	// If this was a save-join operation, and the original is empty, then drop it.
	if (!empty($_GET['join-txn'])) {
		$je1 = new AccountJournalEntry($_GET['join-txn']);
		$chk = $je1->getLedgerEntryList();
		if (0 == count($chk)) {
			$je1->delete();
		}
	}

	// Commit and Redirect
	if ('save-copy' == $_POST['a']) {
		// $_SESSION['account-transaction'] = $aje;
		// $_SESSION['account-transaction-list' = array();
		// ] = $ale;
		// // $_SESSION['new-transaction'] = $aje;
		Radix::redirect('/account/transaction');
	}

	// Redirect Back
	$ret = '/account/ledger';
	if (!empty($_SESSION['return-path'])) {
		$ret = $_SESSION['return-path'];
		unset($_SESSION['return-path']);
	}
	Radix::redirect($ret);
	break;
}

if ($id) {

	$this->AccountJournalEntry = new AccountJournalEntry($id); //
	$this->AccountLedgerEntryList = $this->AccountJournalEntry->getLedgerEntryList();

	$this->FileList = $this->AccountJournalEntry->getFiles();

} elseif (!empty($_SESSION['account-transaction'])) {

	$this->AccountJournalEntry = $_SESSION['account-transaction']->AccountJournalEntry;
	$this->AccountLedgerEntryList = $_SESSION['account-transaction']->AccountLedgerEntryList;
	// @todo Here on on Save (above)?
	// unset($_SESSION['account-transaction']);

} else {
	// Default Two Line Items
	$this->AccountJournalEntry = new AccountJournalEntry();
	$this->AccountLedgerEntryList = array();
	$this->AccountLedgerEntryList[] = new AccountLedgerEntry();
	$this->AccountLedgerEntryList[] = new AccountLedgerEntry();
}

// Correct Missing Date
if (empty($this->AccountJournalEntry['date'])) {
	$this->AccountJournalEntry['date'] = isset($_SESSION['account']['date']) ? $_SESSION['account']['date'] : date('Y-m-d');
}

// Request to Join a Second Transaction to this JE
if (!empty($_GET['join-txn'])) {

	$je1 = new AccountJournalEntry($_GET['join-txn']);

	$le1_list = $je1->getLedgerEntryList();

	foreach ($le1_list as $x) {
		$this->AccountLedgerEntryList[] = $x;
	}

}

// Add Prev / Next Links
$this->jump_list = array();
if (!empty($this->AccountJournalEntry['id'])) {

	// Prev Five
	$s = sprintf('SELECT id FROM account_journal where id < %d order by id desc limit 5',$this->AccountJournalEntry['id']);
	$r = SQL::fetch_all($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
	// This
	$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$this->AccountJournalEntry['id']);
	// Next Five
	$s = sprintf('SELECT id FROM account_journal where id > %d order by id asc limit 5',$this->AccountJournalEntry['id']);
	$r = SQL::fetch_all($s);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x['id']);
	}
}
