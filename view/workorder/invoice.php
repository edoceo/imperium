<?php
/**
    Work Order Invoice View

    When building invoices and there are currently active ones this allows a user to select one of them

    @todo Let User Change Status/Colour When Posting Invoice!
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\HTML\Form;

echo '<form action="" method="post">';

echo '<div>';
echo Form::hidden('id',$this->WorkOrder['id']);
//echo $this->formHidden('contact_id',$this->WorkOrder->contact_id);
echo '</div>';

echo '<table>';
// Contact & Phone
echo '<tr>';
echo '<td class="b r">Contact:</td><td><a href="' . Radix::link('/contact/view?c=' . $this->Contact['id']) . '">' . html($this->Contact['name']) . '</a></td>';
echo '<td class="b r">Phone:</td><td>' . Radix::block('stub-channel', array('value'=>$this->Contact['phone'])) . '</td>';
echo '</tr>';

// Kind and Date
echo '<tr>';
echo '<td class="b r">Kind:</td><td>' . $this->WorkOrder['kind'] . '</td>';
echo '<td class="b r">Date:</td><td>' . $this->WorkOrder['date'] . '</td>';
echo '</tr>';

// Status
echo '<tr>';
echo '<td class="b r">Status:</td><td><input name="status" type="text" value=""></td>';
echo '</tr>';
echo '</table>';

// Work Order Items
echo '<h2>Choose Work Order Items</h2>';

$full_total = 0;
$bill_total = 0;
$full_quantity = 0;
$bill_quantity = 0;

$x_kind = null;
$x_status = null;

echo '<table>';
//echo '<caption>Work order items captoin</caption>';
echo '<tr><th colspan="3">Quantity</th><th>Kind</th><th>Name</th><th>Rate</th><th>Tax</th><th>Subtotal</th></tr>';
// @todo Need to track Invoice Totals
foreach ($this->WorkOrderItemList as $woi) {

    // Header Row
    if ( ($x_kind != $woi['kind']) || ($x_status != $woi['status']) ) {
        //drawSummaryRow($bill_quantity, $bill_total);
        if ($woi['invoice_id']) {
            $text = Radix::link("/invoice/view/{$woi['invoice_id']}","Invoice #{$woi['invoice_id']}");
        } else {
            $text =  $woi['status'] . ' ' . $woi['kind'] . ' Items';
        }
        // echo "<tr><th colspan='6'>". $text . '</th></tr>';
        $bill_total = 0;
        $bill_quantity = 0;
    }

    $name = html((isset($woi['date']) ? date('m/d/y ',strtotime($woi['date'])).'&nbsp;' : null) . $woi['name']);

    echo '<tr class="rero">';
    echo '<td>' . Form::checkbox('woi_id[]', $woi['id'],array('checked'=>'checked')) . '</td>';
    echo '<td>' . Form::text('woi_q_' . $woi['id'], $woi['a_quantity'], array('style'=>'width:3em')) . '</td>';
    echo '<td>' . Form::text('woi_status_' . $woi['id'], $woi['status']) . '</td>';

    echo '<td class="c">' . $woi['kind'] .'</td>';
    //echo '<td><strong>' . $this->link('/workorder.item/view/' . $woi->id,$name) . '</strong></td>';
    echo '<td>' . $name . '</td>';
    echo "<td class='c'>{$woi['a_rate']}/{$woi['a_unit']}</td>";
    echo '<td class="r">' . tax_rate_format($woi['a_tax_rate']) . '</td>';
    // Sub-Total
    $st = ($woi->a_quantity * $woi->a_rate);
    $st+= ($st * floatval($woi->a_tax_rate));
    echo '<td class="r">' . number_format($st,2)."</td>";
    echo '</tr>';

    // Build Sums
    $full_total += ($woi->a_quantity * $woi->a_rate);
    $full_quantity += $woi->a_quantity;
    
    $bill_total += ($woi->a_quantity * $woi->a_rate);
    $bill_quantity += $woi->a_quantity;
    
    $x_kind = $woi->kind;
    $x_status = $woi->status;

}
drawSummaryRow($bill_quantity,$bill_total);
drawSummaryRow($full_quantity,$full_total,'Total');
echo '</table>';

echo '<div class="cmd">';
echo '<select name="invoice_id">';
foreach ($this->InvoiceList as $k=>$v) {
    echo '<option value="' . $k . '">' . $v . '</option>';
}
echo '</select>';
echo '<input name="id" type="hidden" value="' . $this->WorkOrder->id . '">';
echo '<input name="cmd" type="submit" value="Invoice">';
echo '</div>';

echo '</form>';

function drawSummaryRow($q,$t,$name='Sub Total')
{
	if ($q != 0) {
		echo '<tr>';
		echo '<td class="c"><strong>' . number_format($q,2) . '</strong></td>';
		echo '<td>&nbsp;</td>';
		echo '<td class="b">' . $name . ':</td>';
		echo '<td>&nbsp;</td>';
		echo '<td>&nbsp;</td>';
		echo '<td class="b r">&curren;' . number_format($t,2) . '</td>';
		echo '</tr>';
	}
}

