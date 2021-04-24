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

//echo Radix::block('account-period-input');

// Counters
$cr_sum = 0;
$dr_sum = 0;
$runbal = $this->balanceAlpha;
$prev_b = 0;

//echo '<form method="get">';
//echo '<div style="display:flex; flex-wrap:wrap; font-size:120%; vertical-align:middle; margin:0.25em;">';
//echo '<div style="flex:1 1 auto;">';
//echo Form::select('id', $this->Account['id'], $this->AccountList_Select);
//echo '</div>';
//echo '<div style="flex:1 1 auto;">' . Form::date('d0', $this->date_alpha, array('size'=>12)) . '</div>';
//echo '<div style="flex:1 1 auto;">' . Form::date('d1', $this->date_omega, array('size'=>12)) . '</div>';
//echo '<div style="flex:1 1 auto;"><input name="c" type="submit" value="View"> <input name="c" type="submit" value="Post"></div>';
//echo '</div>';
//echo '</form>';

?>

<div class="container">
<div class="row">
<div class="col-md-6">
<div class="form-group">
	<label>Account:</label>
	<div class="input-group">
		<?= Form::select('id', $this->Account['id'], $this->AccountList_Select, array('class' => 'form-control')) ?>
		<span class="input-group-append">
			<a class="btn btn-outline-primary" href="<?= Radix::link('/account/journal?' . http_build_query($_GET)) ?>"><i class="fa fa-list" title="Journal"></i></a>
			<a class="btn btn-outline-primary" href="<?= Radix::link('/account/edit?id=' . $this->Account['id']) ?>"><i class="fa fa-edit" title="Edit"></i></a>
		</span>
	</div>
</div>
</div>

<div class="col-md-3">
<div class="form-group">
	<label>From:</label>
	<?= Form::date('d0',$this->date_alpha, array('class' => 'form-control')) ?>
</div>
</div>
<div class="col-md-3">
<div class="form-group">
	<label>To:</label>
	<?= Form::date('d1',$this->date_omega, array('class' => 'form-control')) ?>
</div>
</div>
</div> <!-- /.row -->
</div>

<?php

echo Radix::block('account-period-arrow', $this->date_alpha);

// echo ' <a href="' . Radix::link('/account/ledger?' . http_build_query($_GET)) . '"><i class="fa fa-bar-chart">L</i></a>';
// echo ' <a href="' . Radix::link('/account/journal?' . http_build_query($_GET)) . '"><i class="fa fa-">J</i></a>';

echo '<div style="display:flex; flex-wrap:wrap;">';
echo '<div style="flex: 1 1 auto;">';
	echo '<h2>Opening Balance ' . number_format($this->balanceAlpha, 2) . '</h2>';
echo '</div>';
echo '<div style="flex: 1 1 auto;">';
echo '<h2>Closing Balance ' . number_format($this->balanceOmega, 2) . '</h2>';
echo '</div>';
echo '</div>';

?>

<style>
table#account-journal-main {
	margin-bottom: 1em;
	margin-top: 1em;
	position: relative;
}
table#account-journal-main thead tr.open {
	background: #aaa;
}

table#account-journal-main tbody tr.je td {
	font-weight: bold;
}
table#account-journal-main tbody tr.le td.crdr {
	/* border-bottom: 1px solid #aaa; */
	font-weight: bold;
	text-align: right;
	width: 8em;
}
</style>

<table class="table table-sm table-bordered" id="account-journal-main">
<thead class="thead-dark">
	<tr>
	<th>TXN/Code</th>
	<th>Date</th>
	<th>Account/Note</th>
	<th>Debit</th>
	<th>Credit</th>
	<th>Balance</th>
	</tr>
	<tr class="open">
	<th class="c">-Open-</th>
	<th colspan="4"></th>
	<th class="b r"><?= number_format($this->balanceAlpha, 2) ?></th>
	</tr>
</thead>

<tbody>
<?php

$prev_b = $this->balanceAlpha;

$Journal_Entry_Stat = array();

foreach ($this->JournalEntryList as $je) {

	$d = new \DateTime($je['date']);

	$sql = 'SELECT *';
	$sql.= ', CASE account_id WHEN ? THEN 1 ELSE 2 END AS sort';
	$sql.= ' FROM general_ledger WHERE account_journal_id = ?';
	$sql.= ' ORDER BY sort ASC, amount ASC';
	// $sql = sprintf($sql, $je['id']);
	$arg = array($this->Account['id'], $je['id']);
	$res = SQL::fetch_all($sql, $arg);

	ob_start();

	foreach ($res as $le) {

		// Running Balance
		if ($this->Account['id'] == $le['account_id']) {
			if ($le['amount'] < 0) {
				$dr_sum += abs($le['amount']);
				$runbal += abs($le['amount']);
			} else {
				$cr_sum += abs($le['amount']);
				$runbal -= abs($le['amount']);
			}
		} else {
			$x = $le['account_full_name'];
			if (empty($Journal_Entry_Stat[ $x ])) {
				$Journal_Entry_Stat[$x] = $le['amount'];
			} else {
				$Journal_Entry_Stat[$x] += $le['amount'];
			}
		}

		echo '<tr class="le">';

		if ($this->Account['id'] == $le['account_id']) {
			echo '<td colspan="3">' . html($le['account_full_name']) . '</td>';
		} else {
			echo '<td colspan="3" style="text-indent:3em;">';
			echo '<a href="' . Radix::link('/account/journal?id=' . $le['account_id']) . '">';
			echo html($le['account_full_name']);
			echo '</a>';
			echo '</td>';
		}

		// Debit or Credit
		if ($le['amount'] > 0) {
			echo '<td class="crdr"></td><td class="crdr">' . number_format($le['amount'], 2) . '</td>';
		} else {
			echo '<td class="crdr">' . number_format(abs($le['amount']), 2) . '</td><td class="crdr"></td>';
		}

		//if ($le['account_id'] == $this->Account['id']) {
		//	echo '<td class="r">' . number_format($runbal, 2) . '</td>';
		//}
		echo '<td></td>';
		echo '</tr>';
	}

	$le_html = ob_get_clean();
?>

	<tr class="je">
	<td class="c"<?= ($je['flag'] == 0 ? ' style="background:#e00;"' : null) ?>><a href="<?= Radix::link('/account/transaction?id=' . $je['id']) ?>"><?= sprintf('#%s%s', $je['kind'], $je['id']) ?></a></td>
	<td class="c"><?= html($d->format('m/d')) ?></td>
	<td colspan="3"><?= html($je['note']) ?></td>
	<?php
	//if ($runbal > $prev_b) {
		echo '<td class="r">' . number_format($runbal, 2) . '</td>';
	//} else {
	//	echo '<td class="r" style="color:#c00;">' . number_format($runbal, 2) . '</td>';
	//}
	//$prev_b = $runbal;
	?>
	</tr>

	<?= $le_html ?>

<?php
}

?>
</tbody>
<tfoot>
	<tr>
	<td colspan="3">Sum</td>
	<td class="r"><?= number_format($dr_sum, 2) ?></td>
	<td class="r"><?= number_format($cr_sum, 2) ?></td>
	<td class="r"><?= number_format($runbal, 2) ?></td>
	</tr>
</tfoot>
</table>

<?= Radix::block('account-period-arrow', $this->date_alpha); ?>

<section>
<h2>Offset Summary</h2>
<table>
<?php
asort($Journal_Entry_Stat);
// Radix::dump($Journal_Entry_Stat)
foreach ($Journal_Entry_Stat as $a => $v) {
	echo '<tr>';
	echo '<td>' . html($a) . '</td>';
	if ($v < 0) {
		echo '<td class="r">' . number_format(abs($v), 2) . '</td>';
		echo '<td></td>';
	} else {
		echo '<td></td>';
		echo '<td class="r">' . number_format($v, 2) . '</td>';
	}
	echo '</tr>';
}
?>
</table>
</section>

<?php

// echo Radix::dump($res);
// Radix::dump($Journal_Entry_Stat);
