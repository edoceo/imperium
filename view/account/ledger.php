<?php
/**
  Account Ledger View
  Displays a list of the transactions in a ledger style format - showing only one account

*/

$AccountList = array();
$AccountList[-1] = 'All - General Ledger';
foreach ($this->AccountList as $item) {
    $AccountList[$item['id']] = $item['full_name'];
}
$_ENV['title'][] = count($this->LedgerEntryList) . ' entries';

echo '<form method="get">';
echo '<table>';
echo '<tr><td class="b r">Account:</td><td colspan="4">' . radix_html_form::select('id', $this->Account['id'], $AccountList) . "</td></tr>";
echo '<tr>';
echo '<td class="l">From:</td>';
echo "<td>" . radix_html_form::date('d0',$this->date_alpha,array('size'=>12)) . "</td>";
echo '<td class="b c">&nbsp;to&nbsp;</td>';
echo "<td>" . radix_html_form::date('d1',$this->date_omega,array('size'=>12)) . "</td>";
echo "<td><input class='cb' name='c' type='submit' value='View' /></td>";
echo '<td><input name="c" type="submit" value="Post" /></td>';
echo '</tr>';
echo '</table>';
echo '</form>';

// View Results

$balance_x = $this->openBalance;

echo '<table>';
echo '<tr><th>Date</th><th>Account/Note</th><th>Entry #</th><th>Link</th><th>Debit</th><th>Credit</th><th>Balance</th></tr>';

echo '<tr class="rero">';
echo '<td class="c">-Open-</td><td colspan="5">Opening Balance</td>';
echo '<td class="b r">' . number_format($this->openBalance, 2) . '</td>';
echo '</tr>';

foreach ($this->LedgerEntryList as $le)
{
	//$date = AppHelper::dateNice($le['date']);
	//$link = '/accounts/journal/entry/'.$le['account_journal_id'];

    echo '<tr class="rero">';

    echo '<td class="c"><a href="' . radix::link('/account/transaction?id=' . $le['account_journal_id']) . '">' . $le['date'] . '</td>';
    echo '<td>' . $le['account_name'] . '/' . $le['note'] . '</td>';
    echo sprintf('<td class="c">#%s%s</td>',$le['kind'], $le['account_journal_id']);

    // Object Link
    if (!empty($le['link_to'])) {
        echo sprintf('<td class="c">%s:%d</td>', $le['link_to'], $le['link_id']);
    } else {
        echo '<td></td>';
    }

    // Debit or Credit
	if ($le['amount'] > 0) {
		echo "<td>&nbsp;</td><td class='r'>" . number_format($le['amount'],2)."</td>";
	} else {
		echo "<td class='r'>".number_format(abs($le['amount']),2)."</td><td>&nbsp;</td>";
	}

    // Amount
    if (substr($this->Account['kind'],0,5)=='Asset') {
        if ($le['amount'] < 0) {
            $balance_x += abs($le['amount']);
        } else {
            $balance_x -= abs($le['amount']);
        }
    } else {
        $balance_x += $le['amount'];
    }

    echo '<td class="r">' . number_format($balance_x,2) . '</td>';
    echo '</tr>';

}

echo '<tr class="ro">';
echo '<td class="b" colspan="4">Total:</td>';
echo '<td class="b r">&curren;' . number_format($this->dr_total,2) . '</td>';
echo '<td class="b r">&curren;' . number_format($this->cr_total,2) . '</td>';
//if (substr($this->Account->kind,0,5)=='Asset') {
//	echo '<td class="b r">&curren;' . number_format($balance_x * -1,2) . '</td>';
//} else {
	echo '<td class="b r">&curren;' . number_format($balance_x,2) . '</td>';
//}
echo '</tr>';
echo '</table>';
