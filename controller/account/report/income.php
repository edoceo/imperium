<?php
/**
	@file
	@brief Income Statement
*/

// $db = Zend_Registry::get('db');

switch ($this->Period) {
case 'm':
	$_ENV['title'] = 'Income Statement: ' . $this->date_alpha_f;
	break;
case 'q':
	$_ENV['title'] = 'Income Statement: Q:' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
case 'y':
	$_ENV['title'] = 'Income Statement: Y:' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
default:
	$_ENV['title'] = 'Income Statement: ' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
}

// Revenues
$sql = " select a.id as account_id,a.code as account_code, a.full_code, a.name as full_name,sum(b.amount) as balance from ";
$sql.= " account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where a.kind like 'Revenue%' and c.date >= '{$this->date_alpha}' and c.date<='{$this->date_omega}' ";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
$sql.= " group by a.id,a.full_code,a.code,a.name ";
$sql.= " order by a.full_code,a.code ";
//echo "<p>$sql</p>";
$this->RevenueAccountList = radix_db_sql::fetchAll($sql);
radix_session::flash('fail', radix_db_sql::lastError());

// Expenses
$sql = " select a.id as account_id,a.code as account_code, a.full_code, a.name as full_name,sum(b.amount) as balance from ";
$sql.= " account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where a.kind like 'Expense%' and c.date >= '{$this->date_alpha}' and c.date<='{$this->date_omega}' ";
if (intval($_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
$sql.= " group by a.id,a.full_code,a.code,a.name ";
$sql.= " order by a.full_code,a.code ";
//echo "<p>$sql</p>";
$this->ExpenseAccountList = radix_db_sql::fetchAll($sql);
radix_session::flash('fail', radix_db_sql::lastError());