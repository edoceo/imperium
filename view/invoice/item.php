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

use Edoceo\Radix\HTML\Form;

$q = Form::number('quantity', $this->InvoiceItem['quantity'], array('size'=>8));
$r = Form::number('rate', $this->InvoiceItem['rate'], array('size'=>8));
$u = Form::select('unit', $this->InvoiceItem['unit'], $this->UnitList);

?>

<form method="post">
<div class="pure-g">
	<div class="pure-u-1-5"><div class="l">Kind:</div></div>
	<div class="pure-u-1-5"><?= Form::select('kind', $this->InvoiceItem['kind'], InvoiceItem::$kind_list) ?></div>
	<div class="pure-u-1-5"><div class="l">Date:</div></div>
	<div class="pure-u-2-5"><?= Form::date('date', $this->InvoiceItem['date'], array('id'=>'woi_date','size'=>12)) ?></div>

	<div class="pure-u-1-5"><div class="l">Name:</div></div>
	<div class="pure-u-4-5"><td colspan="3"><?= Form::text('name', $this->InvoiceItem['name']) ?></div>

	<div class="pure-u-1-5"><div class="l">Note:</div></div>
	<div class="pure-u-4-5"><textarea name="note"><?= html($this->InvoiceItem['note']) ?></textarea></div>

	<div class="pure-u-1-5"><div class="l">Cost:</div></div>
	<div class="pure-u-2-5"><?= $q ?> <strong>@</strong> <?=$r ?> <strong>per</strong> <?=$u?></div>
	<div class="pure-u-1-5"><div class="l">Tax:</div></div>
	<div class="pure-u-1-5"><input maxlength="6" name="tax_rate" size="5" step="0.001" type="number" value="<?= tax_rate_format($this->InvoiceItem['tax_rate']) ?>">%</div>

	<div class="pure-u-1-5"><div class="l" title="Input and email address here and a notification email will be sent">Notify:</div></div>
	<div class="pure-u-4-5"><?= Form::text('notify', $this->WorkOrderItem['notify']) ?></div>

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
echo '<button class="good" name="a" type="submit" value="save">Save</button>';
if (!empty($this->InvoiceItem['id'])) {
    echo '<button class="fail" name="a" type="submit" value="delete">Delete</button>';
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
	$('#quantity').focus();

	$('input[type=number]').on('blur', function() {
		toNumeric(this);
	});
});

</script>