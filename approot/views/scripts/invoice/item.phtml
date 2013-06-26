<?php
/**
    Invoice Items View

    Shows details about a Invoice Item

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

$n = $this->formText('name',$this->InvoiceItem->name,array('style'=>'width:90%;'));
$q = $this->formText('quantity',$this->InvoiceItem->quantity,array('onblur'=>'toNumeric(this);','size'=>8));
$r = $this->formText('rate',$this->InvoiceItem->rate,array('onblur'=>'toNumeric(this);','size'=>8));
$u = $this->formSelect('unit',$this->InvoiceItem->unit,null,$this->UnitList);

echo '<div>';

echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
echo '<table>';

// Kind & Date
$k = $this->formSelect('kind',$this->InvoiceItem->kind,null,InvoiceItem::$kind_list);
$d = $this->formText('date',$this->InvoiceItem->date,array('id'=>'woi_date','size'=>12));
echo "<tr><td class='b r'>Kind:</td><td>$k</td><td class='b r'>Date:</td><td>$d</td></tr>";

echo "<tr><td class='l'>Cost:</td><td>$q&nbsp;<strong>@</strong>&nbsp;$r&nbsp;<strong>per</strong>&nbsp;$u</td>";
echo '<td class="b r">Tax:</td><td><input maxlength="6" name="tax_rate" onblur="toNumeric(this);" size="5" type="text" value="' . tax_rate_format($this->InvoiceItem->tax_rate) .'"></td>';
echo '</tr>';

// Name
echo '<tr><td class="l">Name:</td><td colspan="3">' . $n . '</td></tr>';

// Note
echo '<tr><td class="l">Note:</td><td colspan="3"><textarea name="note" style="width:90%;">'. html($this->InvoiceItem->note) . '</textarea></td></tr>';

// Notify
echo "<tr><td class='b r'><span title='Input and email address here and a notification email will be sent'>Notify:</span></td><td colspan='3'>".$this->formText('notify',$this->WorkOrderItem->notify,array('size'=>'64'))."</td></tr>";

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

echo '<div class="cmd">';
// echo $this->formHidden('id',$this->Invoice->id);
echo $this->formHidden('invoice_id',$this->Invoice->id);
echo $this->formSubmit('cmd','Save');
echo $this->formSubmit('cmd','Delete');
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

<script type="text/javascript">
$('#quantity').focus();
</script>