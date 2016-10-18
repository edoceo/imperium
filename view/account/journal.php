<?php
/**
	Account Journal View
	Displays a list of the transactions in a journal style format (showing all affected accounts)
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\HTML\Form;

$_ENV['title'] = array(
	'Accounts',
	'Journal',
	$this->Account['full_name'],
	sprintf('%s to %s', $this->date_alpha_f, $this->date_omega_f),
	sprintf('%d entries', count($this->JournalEntryList))
);

echo '<form method="get">';
echo '<table>';
echo '<tr><td class="b r">Account:</td><td colspan="4">' . Form::select('id', $this->Account['id'], $this->AccountList_Select) . "</td></tr>";
echo '<tr>';
echo '<td class="l">From:</td>';
echo "<td>" . Form::date('d0', $this->date_alpha, array('size'=>12)) . "</td>";
echo '<td class="b c">&nbsp;to&nbsp;</td>';
echo "<td>" . Form::date('d1', $this->date_omega, array('size'=>12)) . "</td>";
echo "<td><input class='cb' name='c' type='submit' value='View' /></td>";
echo '<td><input name="c" type="submit" value="Post" /></td>';
echo '</tr>';
echo '</table>';
echo '</form>';

//
$runbal = $this->openBalance;
$cr_sum = 0;
$dr_sum = 0;

?>

<style>
table tbody tr.je {
	background: #666;
}
table tbody td {
	border: 1px solid #333;
}
</style>

<table>
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
foreach ($this->JournalEntryList as $je) {
?>
	<tr class="je">
    <td class="c"><a href="<?= Radix::link('/account/transaction?id=' . $je['id']) ?>"><?= html($je['date']) ?></a></td>
	<td><?= html($je['note']) ?></td>
	<td class="c" colspan="4"><?= sprintf('#%s%s', $je['kind'], $je['id']) ?></td>
	</tr>
<?php

	$sql = 'SELECT *, CASE account_id WHEN ? THEN 1 ELSE 2 END AS sort FROM general_ledger WHERE account_journal_id = ?';
	$sql.= ' ORDER BY sort ASC, amount ASC';
	// $sql = sprintf($sql, $je['id']);
	$arg = array($this->Account['id'], $je['id']);
	$res = SQL::fetch_all($sql, $arg);
	// echo SQL::lastError();

	if (count($res) < 2) {
		die("Bad Journal Entry");
	}

	foreach ($res as $le) {
?>
		<tr>
		<td>-</td>
		<td colspan="2">
		<?= html($le['account_full_name']) ?>
		</td>
<?php
		// Debit or Credit
		if ($le['amount'] > 0) {
			echo '<td></td><td class="r">' . number_format($le['amount'],2) . '</td>';
		} else {
			echo "<td class='r'>" . number_format(abs($le['amount']),2) . '</td><td></td>';
		}

		if ($this->Account['id'] == $le['account_id']) {
			// Amount
			if ($le['amount'] < 0) {
				$dr_sum += abs($le['amount']);
				$runbal += abs($le['amount']);
			} else {
				$cr_sum += abs($le['amount']);
				$runbal -= abs($le['amount']);
			}
			echo '<td class="r">' . number_format($runbal, 2) . '</td>';
		} else {
			echo '<td></td>';
		}

?>
		</tr>
<?php
	}
}

?>
</tbody>
</table>

<script>
$("#d0").datepicker();
$("#d1").datepicker();
</script>
