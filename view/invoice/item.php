<?php
/**
    Invoice Items View

    Shows details about a Invoice Item

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

$n = radix_html_form::text('name', $this->InvoiceItem['name']);
$q = radix_html_form::number('quantity', $this->InvoiceItem['quantity'], array('size'=>8));
$r = radix_html_form::number('rate', $this->InvoiceItem->rate, array('size'=>8));
$u = radix_html_form::select('unit', $this->InvoiceItem->unit, $this->UnitList);

echo '<div>';

echo '<form method="post">';
echo '<table>';

// Kind & Date
$k = radix_html_form::select('kind', $this->InvoiceItem['kind'], InvoiceItem::$kind_list);
$d = radix_html_form::date('date', $this->InvoiceItem['date'], array('id'=>'woi_date','size'=>12));

echo "<tr><td class='b r'>Kind:</td><td>$k</td><td class='b r'>Date:</td><td>$d</td></tr>";

echo "<tr><td class='l'>Cost:</td><td>$q&nbsp;<strong>@</strong>&nbsp;$r&nbsp;<strong>per</strong>&nbsp;$u</td>";
echo '<td class="b r">Tax:</td><td><input maxlength="6" name="tax_rate" size="5" type="number" value="' . tax_rate_format($this->InvoiceItem->tax_rate) .'"></td>';
echo '</tr>';

// Name
echo '<tr><td class="l">Name:</td><td colspan="3">' . $n . '</td></tr>';

// Note
echo '<tr><td class="l">Note:</td><td colspan="3"><textarea name="note">'. html($this->InvoiceItem['note']) . '</textarea></td></tr>';

// Notify
echo '<tr>';
echo '<td class="l">';
echo '<span title="Input and email address here and a notification email will be sent">Notify:</span>';
echo '</td>';
echo '<td colspan="3">' . radix_html_form::text('notify', $this->WorkOrderItem['notify']) . '</td>';
echo '</tr>';

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

echo '</table>';

// Buttons
echo '<div class="cmd">';
echo '<input name="invoice_id" type="hidden" value="' . $this->Invoice['id'] . '">';
echo '<button class="good" name="a" type="submit" value="save">Save</button>';
if (!empty($this->InvoiceItem['id'])) {
    echo '<button class="good" name="a" type="submit" value="delete">Delete</button>';
}
echo '</div>';

echo '</form>';
echo '</div>';

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