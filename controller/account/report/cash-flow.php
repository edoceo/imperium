<?php
/**
 * Cash Flow Statement
 */

use Edoceo\Radix\DB\SQL;

switch ($this->Period) {
	case 'm':
		$_ENV['h1'] = $_ENV['title'] = 'Cash Flow: ' . $this->date_alpha_f;
		break;
	default:
		$_ENV['h1'] = $_ENV['title'] = 'Cash Flow: ' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
		break;
}

// $this->view->title = 'Consolidated Cash Flow Statement from ' . $this->view->date_alpha_f . ' to ' . $this->view->date_omega_f;

// Collect Revenue Information
$sql = "select sum(b.amount) as amount ";
$sql.= " from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= "where a.kind like 'Revenue%' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
//$sql.= "group by a.id,a.name,a.code ";
// echo "<p>$sql</p>";
$this->Revenues = SQL::fetch_one($sql);

// Collect Expense Information
$sql = "select sum(b.amount) as amount from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= "where a.kind like 'Expense%' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
// echo "<p>$sql</p>";
$this->Expenses = SQL::fetch_one($sql);

$sql = "select sum(b.amount) as amount from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= "where a.kind like 'Equity: Owners Capital' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
// echo "<p>$sql</p>";
$this->Investments = SQL::fetch_all($sql);

$sql = "select sum(b.amount) as amount from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= "where a.kind like 'Equity: Owners Drawing' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
// echo "<p>$sql</p>";
$this->Drawings = SQL::fetch_one($sql);

$this->NetIncome = $this->Revenues - $this->Expenses;
