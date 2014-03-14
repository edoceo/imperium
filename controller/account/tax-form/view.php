<?php
/**
	View a Specific Form
*/

require_once('Account/TaxForm.php');
require_once('Account/TaxFormLine.php');

$this->Form = new AccountTaxForm(intval($_GET['id']));
$this->LineList = array();

$list = $this->Form->getTaxLines();

foreach ($list as $line) {

	$line['balance'] = 0;
	$line['accounts'] = array();

	$res = radix_db_sql::fetchAll("select id,name from account where account_tax_line_id = {$line['id']}");
	foreach ($res as $x) {
		$a = new Account($x);
		$a['balance'] = $a->balanceAt($this->date_omega);
		// $a['balance'] = $a->balanceSpan($this->data_alpha, $this->date_omega);
		$line['balance'] += $a['balance'];
		$line['accounts'][] = array(
	 		'name' => $a['name'],
	 		'balance' => $a['balance'],
		);
	}
	$this->LineList[] = $line;
	// $list[] = $item;
}
