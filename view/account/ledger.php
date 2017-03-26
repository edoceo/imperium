<?php
/**
	Account Ledger View
	Displays a list of the transactions in a ledger style format - showing only one account
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

$_ENV['title'] = array(
	'Accounts',
	'Ledger',
	$this->Account['full_name'],
	sprintf('%s to %s', $this->date_alpha_f, $this->date_omega_f),
	sprintf('%d entries', count($this->LedgerEntryList))
);

// echo Radix::block('account-period-input');

echo '<form method="get">';
echo '<table>';
echo '<tr><td class="b r">Account:</td><td colspan="4">' . Form::select('id', $this->Account['id'], $this->AccountList_Select);
echo ' <a href="' . Radix::link('/account/journal?' . http_build_query($_GET)) . '"><i class="fa fa-list" title="Journal"></i></a>';
echo ' <a href="' . Radix::link('/account/edit?id=' . $this->Account['id']) . '"><i class="fa fa-edit" title="Edit"></i></a>';
echo '</td></tr>';
echo '<tr>';
echo '<td class="l">From:</td>';
echo "<td>" . Form::date('d0',$this->date_alpha,array('size'=>12)) . "</td>";
echo '<td class="b c">&nbsp;to&nbsp;</td>';
echo "<td>" . Form::date('d1',$this->date_omega,array('size'=>12)) . "</td>";
echo "<td><input class='cb' name='c' type='submit' value='View' /></td>";
echo '<td><input name="c" type="submit" value="Post" /></td>';
echo '</tr>';
echo '</table>';
echo '</form>';


echo Radix::block('account-period-arrow', $this->date_alpha);

//
$runbal = $this->openBalance;
$cr_sum = 0;
$dr_sum = 0;

// View Results
?>
<table style="width:100%;">
<thead>
	<tr>
	<th>Date</th>
	<th>Account/Note</th>
	<th>Entry #</th>
	<th>Debit</th>
	<th>Credit</th>
	<th>Balance</th>
	</tr>
</thead>

<tbody>

<tr class="rero">
<td class="c">-Open-</td><td colspan="4">Opening Balance</td>
<td class="b r"><?= number_format($this->openBalance, 2) ?></td>
</tr>

<?php
foreach ($this->LedgerEntryList as $le)
{
	//$date = AppHelper::dateNice($le['date']);
	//$link = '/accounts/journal/entry/'.$le['account_journal_id'];
	$link = '/account/transaction?' . http_build_query(array(
		'id' => $le['account_journal_id'],
		'r' => '/account/ledger?' . http_build_query(array('id' => $this->Account['id'])),
	));

    echo '<tr class="rero">';

    echo '<td class="c"><a href="' . Radix::link($link) . '">' . $le['date'] . '</td>';
    echo '<td>' . html($le['note']) . '</td>';
    echo sprintf('<td class="c">#%s%s</td>', $le['kind'], $le['account_journal_id']);

    // Object Link
    //if (!empty($le['link_to'])) {
    //    echo sprintf('<td class="c">%s:%d</td>', $le['link_to'], $le['link_id']);
    //} else {
    //    echo '<td></td>';
    //}

    // Debit or Credit
	if ($le['amount'] > 0) {
		echo '<td></td><td class="r">' . number_format($le['amount'],2) . '</td>';
	} else {
		echo "<td class='r'>" . number_format(abs($le['amount']),2) . '</td><td></td>';
	}

    // Amount
	if ($le['amount'] < 0) {
		$dr_sum += abs($le['amount']);
		$runbal += abs($le['amount']);
	} else {
		$cr_sum += abs($le['amount']);
		$runbal -= abs($le['amount']);
	}
    //if (substr($this->Account['kind'],0,5)=='Asset') {
    //} else {
    //    $runbal += $le['amount'];
    //}

    echo '<td class="r">' . number_format($runbal, 2) . '</td>';
    echo '</tr>';

}

echo '<tr class="ro">';
echo '<td class="b" colspan="3">Total:</td>';
echo '<td class="b r">&curren;' . number_format($dr_sum, 2) . '</td>';
echo '<td class="b r">&curren;' . number_format($cr_sum, 2) . '</td>';
//if (substr($this->Account->kind,0,5)=='Asset') {
//	echo '<td class="b r">&curren;' . number_format($runbal * -1,2) . '</td>';
//} else {
	echo '<td class="b r">&curren;' . number_format($runbal,2) . '</td>';
//}
echo '</tr>';
echo '</table>';

echo Radix::block('account-period-arrow', $this->date_alpha);
