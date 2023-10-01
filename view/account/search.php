<?php
/**
	@file
	@brief Search the Account System
*/

$q_kind = 'open';
$q_term = trim($_GET['q']);

if (is_numeric($q_term)) {
	$q_kind = 'amount-id';
} elseif (preg_match('/^\$\s*[\d\.,]+/', $q_term, $m)) {
	$q_kind = 'amount';
	$q_term = preg_replace('/[^\d\.]+/', null, $q_term);
	$q_term = str_replace(',','',$q_term); // Strip Comma (in USA)
	$q_term = floatval($m[1]);
}

// Build SQL
$arg = array();
$sql = 'SELECT * FROM general_ledger WHERE ';
switch ($q_kind) {
case 'amount-id':
	$sql.= ' abs(amount) = ? OR account_journal_id = ? ';
	$arg[] = abs($q_term);
	$arg[] = intval($q_term);
	break;
case 'amount':
	$sql.= ' abs(amount) = ? ';
	$arg[] = abs($q_term);
	break;
case 'open':
default:
	$sql.= ' note ~* ? ';
	$arg[] = $q_term;
	// OR date = ? ';
}

$sql.= ' ORDER BY date DESC, kind DESC, account_journal_id, amount ';

$res = SQL::fetch_all($sql, $arg);
if (empty($res) || (0 == count($res))) {
	Session::flash('info', 'No Matching Transactions');
	return(0);
}

echo '<table class="table">';
foreach ($res as $rec) {

	echo '<tr>';
	if ($rec['account_journal_id'] != $account_journal_id_x) {
		// Date
		echo '<td class="c"><a href="' . Radix::link('/account/transaction?id=' . $rec['account_journal_id']) . '">' . date('m/d/y', strtotime($rec['date'])) . '</a></td>';
		// Name & Note
		echo '<td>' . html($rec['note']) . '</td>';
		echo '</tr>';
		echo '<tr><td>&nbsp;</td>';
	} else {
		echo '<td>&nbsp;</td>';
	}
	echo '<td>' . $rec['account_full_name'] . '</td>';
	// echo $le['account_full_name'];
	// if (strlen($le->note)) {
	// echo ' <span class="s">(' .  $le->note  . ')</span>';
	// }
	// echo '</td>';

	// Journal Entry ID
	// echo '<td>' . $le->kind . $le->account_journal_id . '</td>';

	// Debit / Credit
	if ($rec['amount'] < 0) {
		echo '<td class="r">' . number_format(abs($rec['amount']),2) . '</td><td>&nbsp;</td>';
		// $dr_total += $le->amount;
	} else {
		echo '<td>&nbsp;</td><td class="r">' . number_format(abs($rec['amount']),2) . '</td>';
		// $cr_total += $le->amount;
	}
	// echo '<td class="r">' . number_format( ($cr_total + $dr_total) * -1,2) . '</td>';
	echo '</tr>';

	$account_journal_id_x = $rec['account_journal_id'];

}
echo '</table>';
