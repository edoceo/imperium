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

//
$runbal = $this->balanceAlpha;
$cr_sum = 0;
$dr_sum = 0;

// View Results
ob_start();

?>
<table class="table">
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
<td class="b r"><?= number_format($this->balanceAlpha, 2) ?></td>
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
    echo sprintf('<td class="c"%s>#%s%s</td>',
    	($le['flag'] == 0 ? ' style="background:#e00;"' : null),
    	$le['kind'],
    	$le['account_journal_id']);

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

$html_table = ob_get_clean();

// Output Begins
?>
<form method="get">
<div class="container">

<div class="row">
<div class="col-md-6">
<div class="form-group">
	<label>Account:</label>
	<div class="input-group">
		<?= Form::select('id', $this->Account['id'], $this->AccountList_Select, array('class' => 'form-control')) ?>
		<span class="input-group-btn">
			<a class="btn" style="width: 3em;" href="<?= Radix::link('/account/journal?' . http_build_query($_GET)) ?>"><i class="fa fa-list" title="Journal"></i></a>
			<a class="btn" style="width: 3em;" href="<?= Radix::link('/account/edit?id=' . $this->Account['id']) ?>"><i class="fa fa-edit" title="Edit"></i></a>
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
	<button class="btn" name="c" type="submit" value="post">Post</button>
</div>
</div> <!-- /.row -->

</div> <!-- /.container -->
</form>

<?php

echo '<div class="container-fluid">';

echo Radix::block('account-period-arrow', $this->date_alpha);

echo '<div class="row">';
echo '<div class="col-md-3">';
	echo '<h2 class="c">Opening: ' . number_format($this->Account->balanceBefore($this->date_alpha), 2) . '</h2>';
echo '</div>';

echo '<div class="col-md-3">';
	echo '<h2 class="c">Debits: ' . number_format($dr_sum, 2) . '</h2>';
echo '</div>';

echo '<div class="col-md-3">';
	echo '<h2 class="c">Credits: ' . number_format($cr_sum, 2) . '</h2>';
echo '</div>';


echo '<div class="col-md-3">';
echo '<h2 class="c">Closing: ' . number_format($this->Account->balanceAt($this->date_omega), 2) . '</h2>';
echo '</div>';
echo '</div>'; // /.row

echo $html_table;

echo Radix::block('account-period-arrow', $this->date_alpha);

echo '</div>';