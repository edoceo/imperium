<?php
/**
	An AJAX Handler
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

$q = strtolower(trim($_GET['term']));
if (strlen($q) == 1) {
	$q = '^' . $q;
}

$action = $_GET['a'];
if (!empty($_POST['a'])) {
	$action = $_POST['a'];
}

switch ($action) {
case 'account':

	// $s = $this->_d->select();
	// $s->from('account',array('id','full_name as label','full_name as result'));
	// $s->where('name ~* ?',$q);
	// $s->orWhere('full_name ~* ?','^'.$q);
	// // $s->orWhere('name ~* ?','^'.$q);
	// $s->order(array('full_name'));
	// $r = $this->_d->fetchAll($s);

	$sql = 'SELECT DISTINCT id, full_name AS label, full_name AS result';
	$sql.= ' FROM account';
	$sql.= ' WHERE name ~* ? OR full_name ~* ?';
	$sql.= ' ORDER BY full_name';
	$res = SQL::fetch_all($sql, array($q, "^$q"));
	die(json_encode($res));
	break;

case 'contact':

	$s = $this->_d->select();
	$s->from('contact',array('id','name as label','name as result'));
	$s->where('contact ~* ?',$q);
	$s->orWhere('company ~* ?','^'.$q);
	$s->orWhere('name ~* ?','^'.$q);
	$s->order(array('contact'));
	$r = SQL::fetch_all($s);

	echo json_encode($r);

case 'drop-ledger-entry':

	$le = new AccountLedgerEntry($_POST['id']);
	$le->delete();

	header('Content-Type: application/json');
	die(json_encode(array(
		'status' => 'success',
	)));

	break;

case 'join':
case 'join-entry':

	// Join two Ledger Entries to One Journal Entry
	//print_r($_POST);
	SQL::query('BEGIN');

	$je0 = new AccountJournalEntry($_POST['join'][0]);
	$je1 = new AccountJournalEntry($_POST['join'][1]);

	$le1_list = $je1->getLedgerEntryList();

	foreach ($le1_list as $x) {
		$le = new AccountLedgerEntry($x['id']);
		$le['account_journal_id'] = $je0['id'];
		$le['note'] = $je1['note'];
		$le->save();
	}

	$je1->delete();

	SQL::query('COMMIT');

	header('Content-Type: application/json');
	die(json_encode(array(
		'id' => $je0['id'],
	)));

	break;

}

exit(0);
