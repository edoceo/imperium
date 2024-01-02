<?php
/**
 * Work Order Items View
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Shows details about a Work Order Item
 *
 * @copyright  2008 Edoceo, Inc
 * @package    edoceo-imperium
 * @link       http://imperium.edoceo.com
 * @since      File available since Release 1013
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

if (empty($this->WorkOrderItem)) {
    echo '<p class="fail">Failed to load a Work Order Item</p>';
    return;
}

echo '<form action="'. Radix::link('/workorder/item?' . http_build_query(array('id'=>$this->WorkOrderItem['id']))) . '" id="workorder-item-form" method="post">';
?>

<div class="row">
<div class="col-md-12">
	<div class="input-group mb-2">
		<div class="input-group-text">Name:</div>
		<input autofocus class="form-control" name="name" value="<?= __h($this->WorkOrderItem['name']) ?>">
	</div>
</div>
</div>

<div class="input-group mb-2">
	<div class="input-group-text">Note:</div>
	<textarea class="form-control" name="note"><?= __h($this->WorkOrderItem['note']) ?></textarea>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="input-group mb-2">
			<div class="input-group-text">Date:</div>
			<input class="form-control" name="date" type="date" value="<?= __h($this->WorkOrderItem['date']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group mb-2">
			<div class="input-group-text">Start:</div>
			<input class="form-control" name="time_alpha" type="time" value="<?= __h($this->WorkOrderItem['time_alpha']) ?>">
		</div>
	</div>
	<div class="col-md-4">
		<div class="input-group mb-2">
			<div class="input-group-text">Finish:</div>
			<input class="form-control" name="time_omega" type="time" value="<?= __h($this->WorkOrderItem['time_omega']) ?>">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<div class="input-group mb-2">
			<div class="input-group-text">Expect</div>
			<?= Form::number('e_quantity', $this->WorkOrderItem['e_quantity'], [ 'class' => 'form-control', 'placeholder' => 'Quantity' ]); ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="input-group mb-2">
			<div class="input-group-text">@</div>
			<?= Form::number('e_rate', $this->WorkOrderItem['e_rate'], [ 'class' => 'form-control']) ?>
			<?= Form::select('e_unit', $this->WorkOrderItem['e_unit'], Base_Unit::getList(), [ 'class' => 'form-control']) ?>
		</div>
	</div>
        <div class="col-md-3">
                <div class="input-group mb-2">
                        <div class="input-group-text">Tax:</div>
			<?= Form::number('e_tax_rate', tax_rate_format($this->WorkOrderItem['e_tax_rate']), [ 'class' => 'form-control']) ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<div class="input-group mb-2">
			<div class="input-group-text">Actual:</div>
			<?= Form::number('a_quantity', $this->WorkOrderItem['a_quantity'], [ 'class' => 'form-control']) ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="input-group mb-2">
			<div class="input-group-text">@</div>
			<?= Form::number('a_rate', $this->WorkOrderItem['a_rate'], [ 'class' => 'form-control']) ?>
			<?= Form::select('a_unit', $this->WorkOrderItem['a_unit'], Base_Unit::getList(), [ 'class' => 'form-control']) ?>
		</div>
	</div>
	<div class="col-md-3">
		<div class="input-group mb-2">
			<div class="input-group-text">Tax:</div>
			<?= Form::number('a_tax_rate',tax_rate_format($this->WorkOrderItem['a_tax_rate']), [ 'class' => 'form-control']) ?>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="input-group mb-2">
			<div class="input-group-text">Type:</div>
			<?= Form::select('kind', $this->WorkOrderItem['kind'], WorkOrderItem::$kind_list, [ 'class' => 'form-control']) ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="input-group mb-2">
			<div class="input-group-text" title="The Status of this Item, Completed Items will be Billed when creating an Invoice">Status</div>
			<?= Form::select('status',$this->WorkOrderItem['status'], $this->ItemStatusList, [ 'class' => 'form-control' ]) ?>
		</div>
	</div>
</div>

<?php
echo '<table class="table">';

// Notify
echo '<tr><td class="l">';
echo '<span title="Input an email address here and a notification email will be sent">Notify:</span></td>';
echo '<td colspan="5">' . Form::text('notify', $this->WorkOrderItem['notify']) . '</td>';
echo '</tr>';

echo "</table>";

echo '<div class="cmd">';
echo '<input name="workorder_id" type="hidden" value="' . $this->WorkOrder['id'] . '">';
echo '<button class="btn btn-primary me-2 good" id="workorder-item-exec-save" name="a" type="submit" value="save">Save</button>';
if (!empty($this->WorkOrderItem['id'])) {
    echo '<button class="btn btn-danger fail" name="a" type="submit" value="delete">Delete</button>';
}
echo '</div>';

echo '</form>';

// History
$args = array(
    'list' => $this->WorkOrderItem->getHistory()
);
echo Radix::block('diff-list', $args);

?>

<script>
$(function() {

	$('#notify').autocomplete({
		source:'/imperium/contact/ajax',
		change:function(event, ui) { if (ui.item) {  $("#notify").val(ui.item.id); $("#notify").val(ui.item.contact); } }
	});

	// When the times change update the Actual Quantity
	$('#time_alpha, #time_omega').on('change',function() {

		var m = null;

		var alpha = $('#time_alpha').val();
		var h_alpha, m_alpha = 0;
		var h_omega, m_omega = 0;
		var omega = $('#time_omega').val();

		if (m = alpha.match(/^(\d+):(\d+)$/)) {
			h_alpha = parseInt(m[1]);
			m_alpha = parseInt(m[2]) / 60 * 100;
		}

		if (m = omega.match(/^(\d+):(\d+)$/)) {
			h_omega = parseInt(m[1]);
			m_omega = parseInt(m[2]) / 60 * 100;
		}

		if (h_omega < h_alpha) {
			h_omega += 24;
		}

		var h_delta = h_omega - h_alpha;
		var m_delta = Math.abs(m_omega - m_alpha);
		if (m_delta > 0) {
			--h_delta;
			m_delta = 100 - m_delta;
		}

		$('#a_quantity').val(h_delta.toFixed(0) + '.' + m_delta.toFixed(0));
	});

	$('input[type=number]').on('blur', function() {
		toNumeric(this);
	});

	// Bind Ctrl+Enter
	$('#workorder-item-form').on('keypress', function(e) {
		if ((e.keyCode == 10) && (e.ctrlKey)) {
			$('#status').val('Complete');
			$('#workorder-item-exec-save').click();
		}
	});

});

</script>

<?php
// @todo should be at theme or webroot/index.php level
if (Radix::isAJAX()) {
	exit(0);
}
