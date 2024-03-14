<?php
/**
	Account Ledger View
	Displays a list of the transactions in a ledger style format - showing only one account
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

$_ENV['title'] = array(
	'Accounts',
	'Ledger',
	$this->Account['full_name'],
	sprintf('%s to %s', $this->date_alpha_f, $this->date_omega_f),
	sprintf('%d entries', count($this->LedgerEntryList))
);

//
$runbal = $this->balanceAlpha;
$cr_sum = 0;
$dr_sum = 0;

// View Results
ob_start();

?>
<table class="table table-sm table-striped table-hover">
<thead class="table-dark">
	<tr>
		<th>Date</th>
		<th>Account/Note</th>
		<th>Entry #</th>
		<th class="r">Debit</th>
		<th class="r">Credit</th>
		<th class="r">Balance</th>
		<th></th>
	</tr>
	<tr class="table-secondary">
		<td class="c">-Open-</td><td colspan="4">Opening Balance</td>
		<td class="b r"><?= number_format($this->balanceAlpha, 2) ?></td>
		<td></td>
	</tr>
</thead>
<tbody>
<?php
foreach ($this->LedgerEntryList as $le)
{
	//$date = AppHelper::dateNice($le['date']);
	//$link = '/accounts/journal/entry/'.$le['account_journal_id'];
	$link = '/account/transaction?' . http_build_query(array(
		'id' => $le['account_journal_id'],
		'r' => '/account/ledger?' . http_build_query(array('id' => $this->Account['id'])),
	));

    echo '<tr>';

    echo '<td class="c"><a href="' . Radix::link($link) . '">' . $le['account_ledger_date'] . '</td>';
    echo '<td>' . html($le['note']) . '</td>';
	printf('<td class="c"%s>#%s%s</td>'
		, ($le['flag'] == 0 ? ' style="color:#f00; font-weight:bold;"' : null)
		, $le['account_journal_type']
		, $le['account_journal_id']
	);

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

    echo '<td class="r">' . number_format($runbal, 2) . '</td>';
    echo '<td class="r"><button class="btn btn-sm join-entry" data-id="' . $le['account_journal_id'] . '"><i class="fas fa-compress-arrows-alt"></i></button></td>';
    echo '</tr>';

}
?>
</tbody>

<tfoot>
<tr>
<td class="b" colspan="3">Total:</td>
<td class="b r">&curren;<?= number_format($dr_sum, 2) ?></td>
<td class="b r">&curren;<?= number_format($cr_sum, 2) ?></td>
<td class="b r">&curren;<?= number_format($runbal, 2) ?></td>
<?php
//if (substr($this->Account->account_type,0,5)=='Asset') {
//	echo '<td class="b r">&curren;' . number_format($runbal * -1,2) . '</td>';
//} else {
//}
?>
</tr>
</tfoot>

</table>

<?php

$html_table = ob_get_clean();

// Output Begins
?>
<form method="get">

<div class="row">
<div class="col-md-6">
<div class="form-group">
	<label>Account:</label>
	<div class="input-group">
		<?= Form::select('id', $this->Account['id'], $this->AccountList_Select, array('class' => 'form-select')) ?>
		<span class="input-group-append">
			<a class="btn btn-outline-primary" href="<?= Radix::link('/account/journal?' . http_build_query($_GET)) ?>"><i class="fas fa-list" title="Journal"></i></a>
			<a class="btn btn-outline-primary" href="<?= Radix::link('/account/edit?id=' . $this->Account['id']) ?>"><i class="fas fa-edit" title="Edit"></i></a>
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

<div class="row">
<div class="col-md-4">
	<button class="btn btn-primary" name="c" type="submit" value="view">View</button>
	<button accesskey="p" class="btn btn-outline-secondary" name="c" type="submit" value="post">Post</button>
</div>
</div> <!-- /.row -->

</form>

<?php

echo Radix::block('account-period-arrow', $this->date_alpha);

echo '<div class="row">';
echo '<div class="col">';
	echo '<h2 class="c">Opening: ' . number_format($this->Account->balanceBefore($this->date_alpha), 2) . '</h2>';
echo '</div>';

echo '<div class="col">';
	echo '<h2 class="c">Debits: ' . number_format($dr_sum, 2) . '</h2>';
echo '</div>';

echo '<div class="col">';
	echo '<h2 class="c">Credits: ' . number_format($cr_sum, 2) . '</h2>';
echo '</div>';

echo '<div class="col">';
	echo '<h2 class="c">Delta:: ' . number_format($dr_sum - $cr_sum, 2) . '</h2>';
echo '</div>';

echo '<div class="col">';
	echo '<h2 class="c">Closing: ' . number_format($this->Account->balanceAt($this->date_omega), 2) . '</h2>';
echo '</div>';
echo '</div>'; // /.row

echo $html_table;

echo Radix::block('account-period-arrow', $this->date_alpha);

ob_start();
?>
<script>
$(function() {

	var join_entry_list = [];

	$('.join-entry').on('click', function() {

		$(this).addClass('btn-warning');

		var lei = $(this).data('id');
		join_entry_list.push(lei);

		// Add this one
		if (join_entry_list.length >= 2) {

			debugger;

			// Merge to a Journal Entry somehow?

			var tx0 = join_entry_list[0];
			var tx1 = join_entry_list[1];

			var arg = {
				a: 'join-entry',
				join: join_entry_list,
			}

			window.location = '<?= Radix::link('/account/transaction') ?>?id=' + tx0 + '&join-txn=' + tx1;

			//$.post('<?= Radix::link('/account/ajax') ?>', arg, function(res, ret) {
			//	if (res.id) {
			//		window.location = '<?= Radix::link('/account/transaction') ?>?id=' + res.id;
			//	}
			//});
            //
			//join_entry_list = [];
			//$('.join-entry').removeClass('btn-warning');
		}

	});
});
</script>
<?php
$code = ob_get_clean();
Layout::addScript($code);
