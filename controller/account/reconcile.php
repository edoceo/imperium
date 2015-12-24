<?php
/**
	@file
	@brief Accepts Uploaded Files, Saves to Journal/Ledger
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

// See View
if ( ($id = intval($_GET['id'])) > 0) {
	$this->Account = new Account($id);
} elseif (!empty($_SESSION['reconcile_upload_id'])) {
	$this->Account = new Account($_SESSION['reconcile_upload_id']);
} elseif (!empty($_ENV['account']['banking_account_id'])) {
	$this->Account = new Account($_ENV['account']['banking_account_id']);
}

// Preveiw
switch (strtolower($_POST['a'])) {
case 'upload': // Read the Uploaded Data

	$_ENV['mode'] = 'view';

	if ($_FILES['file']['error']==0) {
		$this->Account = new Account($_POST['account_id']);
		$_ENV['title'] = array('Account','Reconcile', $this->Account['full_name'], 'Preview');
		// Read File
		$arg = array(
			'kind' => $_POST['format'],
			'file' => $_FILES['file']['tmp_name'],
			'account_id' => $_POST['upload_id'],
		);
		$this->JournalEntryList = Account_Reconcile::parse($arg);
	} else {
		Session::flash('fail', 'Failed to Upload');
	}

	// @todo If the Target Account is Asset then Other Side Only (and vice-versa)
	$sql = 'SELECT id,full_name ';
	$sql.= 'FROM account ';
	// $sql.= "WHERE kind like 'Expense%' ";
	$sql.= 'ORDER BY full_code ASC, code ASC';
	$this->AccountPairList = SQL::fetch_mix($sql);

	$_SESSION['reconcile_upload_id'] = $_POST['upload_id'];
	$_SESSION['reconcile_offset_id'] = $_POST['offset_id'];

	$_ENV['upload_account_id'] = $_SESSION['reconcile_upload_id'];
	$_ENV['offset_account_id'] = $_POST['offset_id'];

	break;

case 'save': // Save the Uploaded Transactions

	$_ENV['upload_account_id'] = $_SESSION['reconcile_upload_id'];

	Radix::dump($_POST);
	return(0);

	$c = ceil(count($_POST) / 4);
	for ($i=1;$i<=$c;$i++) {

		// Skip Entries Missing Date (or the last of the count)
		if (empty($_POST[sprintf('je%ddate',$i)])) {
			continue;
		}
		if (!empty($_POST[sprintf('je%did',$i)])) {
			die("Skip2");
			continue; // Have this one already;
		}

		// Journal Entry
		$je = new AccountJournalEntry();
		$je['auth_user_id'] = $_SESSION['uid'];
		$je['date'] = $_POST[sprintf('je%ddate',$i)]; // $req->getPost('date');
		$je['note'] = $_POST[sprintf('je%dnote',$i)]; // $req->getPost('note');
		$je['kind'] = 'N'; // $req->getPost('kind');
		$je->save();

		// Debit Side
		$dr = new AccountLedgerEntry();
		$dr['auth_user_id'] = $_SESSION['uid'];
		$dr['account_journal_id'] = $je['id'];
		// $dr->account_id = $_POST[sprintf('je%daccount_id')]; // $req->getPost($i . '_account_id');
		// $le->amount = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
		// Bind to an object
		// $le->link_id = $req->getPost($i . '_link_id');
		// $le->link_to = $req->getPost($i . '_link_to');
		// Save Ledger Entry

		// Credit Side
		$cr = new AccountLedgerEntry();
		$cr['auth_user_id'] = $_SESSION['uid'];
		$cr['account_journal_id'] = $je['id'];
		// $cr->account_id = $req->getPost($i . '_account_id');
		// $ale->note = $req->getPost($i . '_note');
		// $cr->amount = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
		// Bind to an object
		// $cr->link_id = $req->getPost($i . '_link_id');
		// $cr->link_to = $req->getPost($i . '_link_to');

		if (!empty($_POST[sprintf('je%dcr',$i)])) {
			// Credit to the Upload Target Account
			$dr['account_id'] = $_POST[sprintf('je%daccount_id',$i)];
			$cr['account_id'] = $_ENV['upload_account_id'];
			$dr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%dcr',$i)])) * -1;
			$cr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%dcr',$i)]));
		} else {
			// Debit to the Upload Target Account
			$dr['account_id'] = $_ENV['upload_account_id'];
			$cr['account_id'] = $_POST[sprintf('je%daccount_id',$i)];
			$dr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%ddr',$i)])) * -1;
			$cr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%ddr',$i)]));
		}
		$dr->save();
		$cr->save();
	}

	Session::flash('info', "Saved $i/$c Transactions");

	break;

case 'save-one': // Save the Uploaded Transactions

	session_write_close();

	header('Content-Type: application/json');

	$_ENV['upload_account_id'] = $_SESSION['reconcile_upload_id'];

	// Radix::dump($_POST);

	if (!empty($_POST['id'])) {
		header('HTTP/1.1 400 Bad Request', true, 400);
		die(json_encode(array(
			'status' => 'failure',
			'detail' => 'ID Exists',
		)));
	}

	// Skip Entries Missing Date (or the last of the count)
	if (empty($_POST['date'])) {
		header('HTTP/1.1 400 Bad Request', true, 400);
		die(json_encode(array(
			'status' => 'failure',
			'detail' => 'Invalid Date',
		)));
	}

	// Journal Entry
	$je = new AccountJournalEntry();
	$je['auth_user_id'] = $_SESSION['uid'];
	$je['date'] = $_POST['date']; // $req->getPost('date');
	$je['note'] = $_POST['note']; // $req->getPost('note');
	$je['kind'] = 'N'; // $req->getPost('kind');
	$je->save();

	// Debit Side
	$dr = new AccountLedgerEntry();
	$dr['auth_user_id'] = $_SESSION['uid'];
	$dr['account_journal_id'] = $je['id'];

	// Credit Side
	$cr = new AccountLedgerEntry();
	$cr['auth_user_id'] = $_SESSION['uid'];
	$cr['account_journal_id'] = $je['id'];

	if (!empty($_POST['cr'])) {

		// Credit to the Upload Target Account
		$cr['account_id'] = $_ENV['upload_account_id'];
		$cr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST['cr']));

		$dr['account_id'] = $_POST['offset_account_id'];
		$dr['amount'] = abs(preg_replace('/[^\d\.]+/',null,$_POST['cr'])) * -1;

	} elseif (!empty($_POST['dr'])) {

		// Debit to the Upload Target Account
		$cr['account_id'] = $_POST['offset_account_id'];
		$cr['amount'] = abs(preg_replace('/[^\d\.]+/',null, $_POST['dr']));

		$dr['account_id'] = $_ENV['upload_account_id'];
		$dr['amount'] = abs(preg_replace('/[^\d\.]+/',null, $_POST['dr'])) * -1;
	}

	$dr->save();
	$cr->save();

	die(json_encode(array(
		'status' => 'success',
		'result' => array(
			'journal_entry_id' => $je['id'],
		),
		'detail' => 'Journal Entry saved',
	)));

	break;

}
