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
$runbal = $this->openBalance;

echo '<form method="get">';
echo '<div style="display:flex; flex-wrap:wrap; font-size:120%; vertical-align:middle; margin:0.25em;">';
echo '<div style="flex:1 1 auto;">';
echo Form::select('id', $this->Account['id'], $this->AccountList_Select);
echo '</div>';
echo '<div style="flex:1 1 auto;">' . Form::date('d0', $this->date_alpha, array('size'=>12)) . '</div>';
echo '<div style="flex:1 1 auto;">' . Form::date('d1', $this->date_omega, array('size'=>12)) . '</div>';
echo '<div style="flex:1 1 auto;"><input name="c" type="submit" value="View"> <input name="c" type="submit" value="Post"></div>';
echo '</div>';
echo '</form>';

// echo ' <a href="' . Radix::link('/account/ledger?' . http_build_query($_GET)) . '"><i class="fa fa-bar-chart">L</i></a>';
// echo ' <a href="' . Radix::link('/account/journal?' . http_build_query($_GET)) . '"><i class="fa fa-">J</i></a>';

echo Radix::block('account-period-arrow', $this->date_alpha);

?>

<style>
table#account-journal-main {
	position: relative;
}
table#account-journal-main thead td {
	background: #aaa;
	font-weight: bold;
}
table#account-journal-main tbody tr.je {
	background: #999;
}
table#account-journal-main tbody td {
	border: 1px solid #333;
	font-weight: bold;
}
table#account-journal-main tfoot td {
	background: #aaa;
	font-weight: bold;
}
</style>

<!--
<table id="account-journal-head" style="display:none; position:fixed; top:0;">
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
</table>
-->

<table id="account-journal-main">
<thead>
	<tr>
	<th>Date</th>
	<th>Account/Note</th>
	<th>Debit</th>
	<th>Credit</th>
	<th>Balance</th>
	</tr>
	<tr>
	<td class="c">-Open-</td><td colspan="3">Opening Balance</td>
	<td class="b r"><?= number_format($this->openBalance, 2) ?></td>
	</tr>
</thead>

<tbody>
<?php

$Journal_Entry_Stat = array();

foreach ($this->JournalEntryList as $je) {

	$d = new \DateTime($je['date']);

?>
	<tr class="je">
    <td class="c"><a href="<?= Radix::link('/account/transaction?id=' . $je['id']) ?>"><?= html($d->format('m/d')) ?></a></td>
	<td colspan="3"><?= html($je['note']) ?></td>
	<td class="c"<?= ($je['flag'] == 0 ? ' style="background:#e00;"' : null) ?>><a href="<?= Radix::link('/account/transaction?id=' . $je['id']) ?>"><?= sprintf('#%s%s', $je['kind'], $je['id']) ?></a></td>
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

		$code = $le['account_code'];
		$name = preg_replace('|^[\d\/\- ]+|', null, $le['account_full_name']);

		if ($le['account_id'] != $this->Account['id']) {
			$code = '<a href="' . Radix::link('/account/journal?id=' . $le['account_id']) . '">+' . $le['account_code'] . '</a>';
		}

?>
		<tr>
		<td><?= $code ?></td>
		<td><?= html($name) ?></td>
<?php
		// Debit or Credit
		if ($le['amount'] > 0) {
			echo '<td></td><td class="r">' . number_format($le['amount'], 2) . '</td>';
		} else {
			echo "<td class='r'>" . number_format(abs($le['amount']), 2) . '</td><td></td>';
		}

		// Running Balance
		if ($this->Account['id'] == $le['account_id']) {
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

		if ($le['account_id'] != $this->Account['id']) {
			$k = sprintf('%s %s', $le['account_code'], $name);
			if (empty($Journal_Entry_Stat[ $k ])) {
				$Journal_Entry_Stat[$k] = $le['amount'];
			} else {
				$Journal_Entry_Stat[$k] += $le['amount'];
			}
		}

	}
}

?>
</tbody>
<tfoot>
	<tr>
	<td colspan="2">Sum</td>
	<td class="r"><?= number_format($dr_sum, 2) ?></td>
	<td class="r"><?= number_format($cr_sum, 2) ?></td>
	<td class="r"><?= number_format($runbal, 2) ?></td>
	</tr>
</tfoot>
</table>

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

<?= $back_next ?>

<script>
// $("#d0").datepicker();
//$("#d1").datepicker();

var fixHead = $("#account-journal-head");
var offMain = $("#account-journal-main").offset().top;

// var $header = $("#table-1 > thead").clone();
// var $fixedHeader
// .append($header);

$(function() {

	//$(window).on('scroll', function() {
    //
	//	var offset = $(this).scrollTop();
	//	offset += 100;
    //
	//	if (offset >= offMain) { // && fixHead.is(":hidden")) {
    //
	//		//fixHead.show();
	//		$("#account-journal-main thead").css('position', 'fixed');
    //
	//		//$("#account-journal-head td").each(function(index) {
	//		//	var index2 = index;
	//		//	$(this).width(function(index2) {
	//		//		return $("#account-journal-main td").eq(index).width();
	//		//	});
	//		//});
    //
	//	} else if (offset < offMain) {
	//		//fixHead.hide();
	//		$("#account-journal-main thead").css('position', 'relative');
	//	}
    //
	//});

});

</script>
