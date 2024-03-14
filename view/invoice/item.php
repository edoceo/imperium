<?php
/**
    Invoice Items View

    Shows details about a Invoice Item

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

if (empty($this->InvoiceItem)) {
	echo '<p class="fail">Failed to load a Invoice Item</p>';
	return;
}

?>

<form action="<?= Radix::link('/workorder/item?' . http_build_query( [ 'id' => $this->InvoiceItem['id'] ])) ?>" id="invoice-item-form" method="post">

<div class="row">
<div class="col-md-6">
	<div class="input-group mb-2">
		<div class="input-group-text">Date:</div>
		<?= Form::date('date', $this->InvoiceItem['date'], [ 'class' => 'form-control'] ) ?>
	</div>
</div>
<div class="col-md-6">
	<div class="input-group mb-2">
		<div class="input-group-text">Kind:</div>
		<?= Form::select('kind', $this->InvoiceItem['kind'], InvoiceItem::$kind_list, [ 'class' => 'form-select' ]) ?>
	</div>
</div>
</div>

<div class="input-group mb-2">
	<div class="input-group-text">Name:</div>
	<input autofocus class="form-control" name="name" value="<?= __h($this->InvoiceItem['name']) ?>">
</div>

<div class="input-group mb-2">
	<div class="input-group-text">Note:</div>
	<textarea class="form-control" name="note"><?= __h($this->InvoiceItem['note']) ?></textarea>
</div>

<div class="row">
<div class="col-md-3">
	<div class="input-group mb-2">
		<div class="input-group-text">Quantity:</div>
		<?= Form::number('quantity', $this->InvoiceItem['quantity'], [ 'class' => 'form-control' ]) ?>
	</div>
</div>
<div class="col-md-3">
	<div class="input-group mb-2">
		<div class="input-group-text">Rate:</div>
		<?= Form::number('rate', $this->InvoiceItem['rate'], [ 'class' => 'form-control'] ) ?>
	</div>
</div>
<div class="col-md-3">
	<div class="input-group mb-2">
		<div class="input-group-text">per</div>
		<?= Form::select('unit', $this->InvoiceItem['unit'], $this->UnitList, [ 'class' => 'form-select']); ?>
	</div>
</div>
<div class="col-md-3">
	<div class="input-group mb-2">
		<div class="input-group-text">Tax:</div>
		<input class="form-control" maxlength="6" name="tax_rate" size="5" step="0.001" type="number" value="<?= tax_rate_format($this->InvoiceItem['tax_rate']) ?>">
		<div class="input-group-text">%</div>
	</div>
</div>
</div>

<div class="input-group mb-2">
	<div class="input-group-text" title="Input an email address here and a notification email will be sent">Notify:</div>
	<?= Form::text('notify', $this->InvoiceItem['notify'], [ 'class' => 'form-control' ]) ?>
</div>

<?php

// @todo Link to Work System (Redmine, Trac, &c)
// if (!empty($this->InvoiceItem->workorder_item_id)) {
//
//     $woi = new WorkOrderItem($this->InvoiceItem->workorder_item_id);
//
//     echo '<tr>';
//     echo '<td class="l">WorkOrder</td>';
//     echo '<td>';
//     echo sprintf('<a href="%s">#%d</a> - Item <a href="%s">#%d</a></td>',
//         $this->link('/workorder/view?w=' . $woi->workorder_id),
//         $woi->workorder_id,
//         $this->link('/workorder/item?id=' . $woi->id),
//         $woi->id);
//     echo '<tr>';
// }

// Buttons
echo '<div class="cmd">';
echo '<input name="invoice_id" type="hidden" value="' . $this->Invoice['id'] . '">';
echo '<button class="btn btn-primary" name="a" type="submit" value="save">Save</button>';
if (!empty($this->InvoiceItem['id'])) {
    echo '<button class="btn btn-danger" name="a" type="submit" value="delete">Delete</button>';
}
echo '</div>';

echo '</form>';

// History
// $args = array(
// //    'ajax' => true,
//     'list' => $this->InvoiceItem->getHistory()
// );
// echo $this->partial('../elements/diff-list.phtml',$args);

?>

<script>
$(function() {
	$('input[type=number]').on('blur', function() {
		toNumeric(this);
	});
});

</script>
