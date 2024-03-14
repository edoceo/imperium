<?php
/**
	Invoice View

	Shows details about an Invoice

	@copyright  2008-2011 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

use Edoceo\Imperium\UI\Button;

$_ENV['title'] = array('Invoice','#' .$this->Invoice['id']);

App::addMRU(Radix::link('/invoice/view?i=' . $this->Invoice['id']), sprintf('Invoice #%d', $this->Invoice['id']));

$contact_address_list = [];
$contact_address_list[] = '-None-';
if (is_array($this->ContactAddressList)) {
	$contact_address_list += $this->ContactAddressList;
}

// Jump to Prev/Next Invoices
if (count($this->jump_list)) {
	$list = array();
	foreach ($this->jump_list as $x) {
		$text = null;
		if ($x['id'] < $this->Invoice['id'] ) {
			$text = '&laquo; #' . $x['id'];
		} elseif ($x['id'] == $this->Invoice['id']) {
			$text = '#' . $x['id'];
		} else {
			$text = '#' . $x['id'] . ' &raquo;';
		}
		$list[] = '<a href="' . Radix::link('/invoice/view?i=' . $x['id']) . '">' . $text . '</a>';
	}
	echo '<div class="jump_list">';
	echo implode(' | ', $list);
	echo '</div>';
}

?>

<form action="<?= Radix::link('/invoice/save?i=' . $this->Invoice['id']) ?>" method="post">

<div class="row">
<div class="col-md-6">
	<div class="input-group mb-2">
		<div class="input-group-text">Contact:</div>
		<?php
		// Contact
		if (empty($this->Contact['id'])) {
			echo '<input class="form-control" id="contact_name" name="contact_name" type="text">';
		} else {
			echo '<input class="form-control" readonly value="' . __h($this->Contact['name']) . '">';
			printf('<a class="btn btn-outline-secondary" href="/contact/view?c=%s"><i class="fa-regular fa-user"></i></a>', $this->Contact['id']);
		}
		?>
	</div>
</div>

<div class="col-md-3">
	<div class="input-group mb-2">
		<label class="input-group-text">Date:</label>
		<?php
		echo Form::date('date',$this->Invoice['date'], array('class' => 'form-control', 'id'=>'iv_date'));
		if ($this->Invoice['due_diff'] < 0) {
			echo '&nbsp;<span class="s">Due in ' . abs($this->Invoice['due_diff']) . ' days</span>';
		} else {
			if ($this->Invoice['status'] != 'Paid') {
				echo '&nbsp;<span class="s">Past Due ' . abs($this->Invoice['due_diff']) . ' days</span>';
			}
		}
		?>
	</div>
</div>

<div class="col-md-3">
	<div class="input-group mb-2">
		<label class="input-group-text">Status:</label>
		<?= Form::select('status', $this->Invoice['status'], $this->StatusList, array('class' => 'form-select')) ?>
	</div>
</div>

</div> <!-- /.row -->

<div class="row">
<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text">Bill To:</label>
		<?= Form::select('bill_address_id', $this->Invoice['bill_address_id'], $contact_address_list, array('class' => 'form-select')) ?>
	</div>
</div>
<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text">Ship To:</label>
		<?= Form::select('ship_address_id', $this->Invoice['ship_address_id'], $contact_address_list, array('class' => 'form-select')) ?>
	</div>

</div>
</div> <!-- /.row -->

<div class="row">
<div class="col-md-12">
	<div class="input-group mb-2">
		<label class="input-group-text">Notes:</label>
		<?= Form::textarea('note',$this->Invoice['note'],array('class' => 'form-control', 'style'=>'height:3em;')) ?>
	</div>
</div>
</div> <!-- /.row -->

<div class="row">
<div class="col-md-6">
	<h2 class="c">Bill Total: <?= number_format($this->Invoice['bill_amount'], 2) ?></h2>
</div>
<div class="col-md-6">
	<h2 class="c">Paid Total: <?php
	if ($this->Invoice['paid_amount'] < $this->Invoice['bill_amount']) {
		echo '<span class="text-danger">' . number_format($this->Invoice['paid_amount'], 2) . '</span>';
	} else {
		echo number_format($this->Invoice['paid_amount'], 2);
	}
	?></h2>
</div>
</div> <!-- /.row -->

<div class="cmd form-actions">
<?= Form::hidden('contact_id', $this->Invoice['contact_id']) ?>
<?= Button::save() ?>
<?= Button::print(Radix::link(sprintf('/invoice/pdf?i=%s', $this->Invoice['id']))) ?>
<?php
// Hawk Monitoring?
if ($this->Invoice->hasFlag(Invoice::FLAG_HAWK)) {
	echo '<button class="btn btn-warning" name="a" type="submit" value="No Hawk">No Hawk</button> ';
} else {
	if ($this->Invoice->canHawk()) {
		echo '<button class="btn btn-secondary" name="a" type="submit" value="Hawk">Hawk</button> ';
	}
}

// Workflow Buttons?
if (!empty($_ENV['invoice.workflow'])) {
	foreach ($_ENV['invoice.workflow'] as $k=>$v) {
		if ( $k == $this->Invoice['status'] ) {
			$list = explode(',',$v);
			foreach ($list as $x) {
				$x = trim($x);
				switch ($x) {
				case 'Delete':
				case 'Void':
					printf('<button class="btn btn-danger" name="a" type="submit" value="%s">%s</button> ', $x, $x);
					break;
				default:
					printf('<button class="btn btn-secondary" name="a" type="submit" value="%s">%s</button> ', $x, $x);
				}
			}
		}
	}
}
?>
</div>
</form>

<?php
// Invoice Notes
if ( ! empty($this->Invoice['id'])) {
	$url = Radix::link('/note/create?i=' . $this->Invoice['id']);
	$arg = array(
		'list' => $this->InvoiceNoteList,
		'page' => $url,
	);
	echo Radix::block('note-list',$arg);
}

// Invoice Items
$item_total = 0;
$item_tax_total = 0;
//$link = Radix::link('/invoice/item');

?>
<section class="mt-4">
<div class="d-flex justify-content-between">
	<div><h2><i class="fas fa-list"></i> Invoice Items</h2></div>
	<div>
		<a accesskey="n" class="btn btn-secondary" href="<?= Radix::link('/invoice/item?i=' . $this->Invoice['id']) ?>">
			Add Item <i class="far fa-plus-square"></i>
		</a>
	</div>
</div>

<div id="invoice-item-edit-wrap"></div>

<?php
if (count($this->InvoiceItemList) > 0) {

	echo '<div id="item-list">';
	echo '<table class="table">';
	echo '<thead class="table-dark">';
	echo '<tr><th>Description</th><th class="r">Quantity</th><th class="r">Rate</th><th class="r">Tax</th><th class="r">Cost</th></tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach ($this->InvoiceItemList as $ivi) {

		$item_subtotal = $ivi['rate'] * $ivi['quantity'];
		$item_total += $item_subtotal;

		echo '<tr>';
		echo '<td class="b"><a href="' . Radix::link('/invoice/item?id=' . $ivi['id']) . '">' .$ivi['name'] . '</a></td>';
		echo '<td class="c b">' .number_format($ivi['quantity'],2) . '</td>';
		echo '<td class="r">' . number_format($ivi['rate'],2) . '/' . $ivi['unit'] . '</td>';
		if ($ivi['tax_rate'] > 0) {
			$item_tax_total += round($item_subtotal * $ivi['tax_rate'],2);
			echo '<td class="r"><sup>' . tax_rate_format($ivi['tax_rate']) . '</sup></td>';
		} else {
			echo '<td class="r">&mdash;</td>';
		}
		echo '<td class="r">' . number_format($item_subtotal, 2) . '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '<tfoot>';
	echo '<tr><td class="b" colspan="4">Sub-Total:</td><td class="l">' . number_format($item_total,2) . '</td></tr>';
	echo '<tr><td class="b" colspan="4">Tax Total:</td><td class="l">' . number_format($item_tax_total,2) . '</td></tr>';
	echo '<tr><td class="b" colspan="4">Bill Total:</td><td class="l">&curren;' . number_format($item_total + $item_tax_total, 2) . '</td></tr>';
	echo '<tr><td class="b" colspan="4">Paid Total:</td><td class="l">&curren;' . number_format($this->Invoice['paid_amount'], 2) . '</td></tr>';
	echo '<tr><td class="b" colspan="4">Balance:</td><td class="l" style="color: #f00;">&curren;' . number_format($item_total + $item_tax_total - $this->Invoice['paid_amount'], 2) . '</td></tr>';
	echo '</tfoot>';
	echo '</table>';
	echo '</div>';
}

// Transactions
if ( count($this->InvoiceTransactionList) > 0) {

	$sum = 0;

	echo '<h2 style="clear:both;"><i class="fas fa-money-check-alt"></i> Transactions</h2>';
	echo '<table class="table">';
	echo '<thead class="table-dark">';
	echo '<tr><th>Date</th><th>Account / Note</th><th>Debit</th><th>Credit</th></tr>';
	echo '</thead>';

	echo '<tbody>';
	foreach ($this->InvoiceTransactionList as $le) {

		$sum+= $le['amount'];

		$link = Radix::link(sprintf('/account/transaction?id=%s', $le['account_journal_id']));

		echo '<tr>';
		echo '<td class="c"><a href="' . $link . '">' . ImperiumView::niceDate($le['date']) . '</a></td>';

		$link = Radix::link(sprintf('/account/journal?id=%s', $le['account_id']));
		echo '<td>';
		printf('<a href="%s">', $link);
		if (strlen($le['note'])) {
			echo ' / ' . $le['note'];
		}
		echo '</a>';
		echo '</td>';
		// todo: debit/credit columns
		if ($le['amount'] < 0) {
			echo "<td class='r'>&curren;".number_format(abs($le['amount']), 2)."</td><td>&nbsp;</td>";
		} else {
			echo "<td>&nbsp;</td><td class='r'>&curren;".number_format($le['amount'], 2)."</td>";
		}
		echo '</tr>';
	}
	echo '</tbody>';
	echo '<tfoot>';
	echo '<tr class="ro">';
	echo '<td class="b" colspan="2">Amount Due:</td>';
	echo '<td><td class="b r">&curren;' . number_format($sum,2) . '</td>';
	echo '</tr>';
	echo '</tfoot>';
	echo '</table>';
}

// History
$args = array('list' => $this->Invoice->getHistory());
echo Radix::block('diff-list',$args);
