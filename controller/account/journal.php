<?php
/**
	Load data for Journal View
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
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

//if (empty($this->Account['id'])) {
//	Session::flash('fail', 'Invalid Account');
//	return(0);
//}

// Show this specific Account
$_SESSION['account-id'] = $this->Account['id'];

$this->balanceAlpha = $this->Account->balanceBefore($this->date_alpha);
$this->balanceOmega = $this->Account->balanceAt($this->date_omega);

// $where = " (account_id = ? OR parent_id = ?) AND (date >= ? AND date <= ?) ";
// $param = array(
// 	$this->Account['id'],
// 	$this->Account['id'],
// 	$this->date_alpha,
// 	$this->date_omega,
// );
// $order = " date,kind desc,amount asc ";
//
// //$this->AccountLedger = $data;
// // $this->dr_total = abs($this->Account->debitTotal($this->date_alpha,$this->date_omega));
// // $this->cr_total = abs($this->Account->creditTotal($this->date_alpha,$this->date_omega));
//
// $sql = "SELECT * FROM general_ledger WHERE $where ORDER BY $order";
// $res = SQL::fetch_all($sql, $param);

// $this->LedgerEntryList = $res;

$where = " (account_id = ?) AND ";
$where.= ' (account_journal.date >= ? AND account_journal.date <= ?) ';
//$where.= " AND account_ledger.amount < 0";
$order = 'account_journal.date, account_journal.kind desc, account_ledger.amount ASC ';

$sql = 'SELECT';
$sql.= ' account_journal.id';
$sql.= ', account_journal.flag';
$sql.= ', account_journal.date';
$sql.= ', account_journal.kind';
$sql.= ', account_journal.note';

$sql.= ' FROM account_journal';
$sql.= ' JOIN account_ledger ON account_journal.id = account_ledger.account_journal_id';

$sql.= " WHERE $where";
$sql.= " ORDER BY $order";

$arg = array(
	$this->Account['id'],
	$this->date_alpha,
	$this->date_omega,
);

$res = SQL::fetch_all($sql, $arg);

$this->JournalEntryList = $res;
