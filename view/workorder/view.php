<?php
/**
    Work Order View

    Displays the main details of the Work Order and allows Edit

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

// Jump List
if (count($this->jump_list)) {
    echo '<div class="jump_list">';
    $list = array();
    foreach ($this->jump_list as $x) {
        if ($x == $this->WorkOrder->id) {
            $list[] = '<span class="hi">#' . $x . '</span>';
        } elseif ($x < $this->WorkOrder->id ) {
            $list[] = '<a href="' . radix::link('/workorder/view?w=' . $x) . '">&laquo; #' . $x . '</a>';
        } else {
            $list[] = '<a href="' . radix::link('/workorder/view?w=' . $x) . '">#' . $x . ' &raquo;</a>';
        }
    }
    echo implode(' | ',$list);
    echo '</div>';
}

echo '<form action="' . radix::link('/workorder/save?w=' . $this->WorkOrder->id) . '" method="post">';
echo star($this->WorkOrder->star ? $this->WorkOrder->star : 'star_' );
echo '<table>';

// Contact & Date Row
echo '<tr>';
if (empty($this->Contact->id)) {
    echo '<td class="l">Contact:</td>';
    echo '<td><input id="contact_name" name="contact_name" type="text" />';
    echo '</td>';
} else {
    echo "<td class='l'>Contact:</td><td>" . radix::link("/contact/view?c={$this->Contact->id}","{$this->Contact->name}");
    echo "<br />Primary Phone: " . radix::block('stub-channel',array('data'=>$this->Contact->phone)) . "</td>";
}
// Date
echo '<td class="l">Date:</td><td>' . radix_html_form::text('date',$this->WorkOrder->date,array('id'=>'wo_date','size'=>12)) . '</td>';
echo '</tr>';

// Requester & Kind
echo '<tr>';
echo '<td class="l">Requester:</td>';
echo '<td>';
echo radix_html_form::text('requester',$this->WorkOrder->requester);
echo '</td>';
// Kind
echo '<td class="l">Kind:</td><td>' . radix_html_form::select('kind',$this->WorkOrder->kind,null,$this->KindList) . '</td>';
echo '</tr>';

// Rate & Units & Status
echo '<tr>';
$r = radix_html_form::text('base_rate',$this->WorkOrder->base_rate,array('class'=>'rate'));
$u = radix_html_form::select('base_unit',$this->WorkOrder->base_unit,null,Base_Unit::getList());
echo "<td class='l'>Base Rate:</td><td class='nw'>$r/$u</td>";
// Status
echo '<td class="l">Status:</td><td>' . $this->WorkOrder->status . '</td>';
echo '</tr>';

// Note
echo '<tr><td class="l">Note:</td><td colspan="3"><textarea name="note">' . html($this->WorkOrder->note). '</textarea></td></tr>';
// echo "<tr><td class='b r'>Hours Total:</td><td colspan='3' style='color: #f00; font-weight: 700; text-align: right;'>".number_format($this->data['WorkOrder']['bill_amount'],2)."</td></tr>";
// echo "<tr><td class='b r'>Parts Total:</td><td colspan='3' style='color: #f00; font-weight: 700; text-align: right;'>".number_format($this->data['WorkOrder']['bill_amount'],2)."</td></tr>";
// Open Total
echo "<tr><td class='l'>Bill Total:</td><td colspan='2' style='font-weight: 700; text-align: right;'>".number_format($this->WorkOrder->bill_amount,2)."</td></tr>";
echo "<tr><td class='l'>Open Total:</td><td colspan='2' style='color: #f00; font-weight: 700; text-align: right;'>".number_format($this->WorkOrder->open_amount,2)."</td></tr>";
echo '</table>';

// Hidden Fields & Buttons
echo '<div class="cmd">';
echo radix_html_form::hidden('id',$this->WorkOrder->id);
echo radix_html_form::hidden('contact_id',$this->WorkOrder->contact_id);
echo radix_html_form::button('c','Save');

if (!empty($_ENV['workorder.workflow'])) {
    $list = array();
    foreach ($_ENV['workorder.workflow'] as $k=>$v) {
        if ( ($k == '*') || ($k == $this->WorkOrder->status) ) {
            $list = explode(',',$v);
            foreach ($list as $x) {
                echo '<input name="c" type="submit" value="' . trim($x) . '" />';
            }
        }
    }
}
echo '</div>';
echo '</form>';

// Work Order Contacts
// echo '<h2>Associated Contacts</h2>';
// if ($list = $this->WorkOrder->getContacts()) {
//     echo $this->partial('../elements/contact-list.phtml',array('list'=>$list));
// }
// echo '<form action="" method="post">';
// echo '<table><tr>';
// echo '<td class="l">Contact</td><td><input id="add_contact_name" name="contact" type="text"></td>';
// echo '<td><input id="add_contact_id" name="add_contact_id" type="hidden" value=""><input name="c" type="submit" value="Add"></td>';
// echo '</tr></table>';
// echo '</form>';

// Work Order Notes
if (!empty($this->WorkOrder->id)) {

    $url = $this->link('/note/create?w=' . $this->WorkOrder->id);
    $arg = array(
        'list' => $this->WorkOrderNoteList,
        'page' => $url,
    );
    echo $this->partial('../elements/note-list.phtml',$arg);
}

// Work Order Files
if (!empty($this->WorkOrder->id)) {

    $url = $this->link('/file/create?w=' . $this->WorkOrder->id);
    $arg = array(
        'list' => $this->WorkOrderFileList,
        'page' => $url,
    );
    echo $this->partial('../elements/file-list.phtml',$arg);
}

// Work Order Items
if (!empty($this->WorkOrder->id)) {

    $url = $this->link('/workorder/item?w=' . $this->WorkOrder->id);

    echo '<h2 id="woi-list">Work Order Items ';
    echo '<a accesskey="n" class="ajax-edit" data-name="woi-edit" href="' . $url . '">';
    echo img('/tango/24x24/actions/list-add.png','Add Item');
    echo '</a>';
    echo '</h2>';
    echo '<div id="woi-edit"></div>';

}

if (count($this->WorkOrderItemList) > 0) {

    $a_size = $e_size = $e_size_full = 0;
    $a_cost = $e_cost = $e_cost_full = 0;

    $x_kind = null;
    $x_status = null;
    $x_invoice_id = -1;

    echo '<table>';
    // echo '<tr><th>Quantity</th><th>Name</th><th>Rate</th><th>Status</th><th>Subtotal</th></tr>';
    // @todo Need to track Invoice Totals
    foreach ($this->WorkOrderItemList as $woi) {

        // Split by Invoice, Kind or Status
        // Summary Row
        if ( ($x_invoice_id != $woi->invoice_id) || ($x_kind != $woi->kind) || ($x_status != $woi->status) ) {

            drawSummaryRow($e_size,$e_cost,$a_size,$a_cost);
            // The method of tying WOI => IVI is incorrect
            //  We should tie IVI => WOI, so that here we'd look for IVI where workorder_item_id = ?
            if ($woi->invoice_id) {
                $text = $this->link("/invoice/view?i={$woi->invoice_id}","Invoice #{$woi->invoice_id}");
            } else {
                $text =  $woi->status . ' ' . $woi->kind . ' Items';
            }
            echo '<tr><th colspan="6">' . $text . '</th></tr>';

            // Reset Counters
            $a_cost = $e_cost = 0;
            $a_size = $e_size = 0;

        }

        // $name = (isset($woi->date) ? date('m/d/y ',strtotime($woi->date)) : null) . $woi->name;
        $link = '<a class="ajax-edit" data-name="woi-edit" href="' . $this->link('/workorder/item?id=' . $woi->id) . '">%s</a>';

        echo '<tr class="rero">';

        // Date
        echo '<td>';
        if (!empty($woi->date)) {
            $html = null;
            $time = strtotime($woi->date);
            $span = $_SERVER['REQUEST_TIME'] - $time;
            $html.= '<span title="' . strftime('%a %Y-%m-%d',$time) . ' (' . floor($span/86400) . ' days ago)">';
            if ($span >= 31104000) { // 360 days
                $html.= strftime('%a %m/%d/%y',$time);
            } else {
                $html.= strftime('%a %m/%d',$time);
            }
            $html.= '</span>';
            echo sprintf($link,$html);
        } else {
            echo '&mdash;';
        }
        echo '</td>';

        // Item
        echo '<td class="b">' . sprintf($link,$woi->name) . '</td>';

        // Estimated Values?
        echo '<td class="r s">';
        if ( (!empty($woi->e_quantity)) && (floatval($woi->e_quantity) > 0) ) {
            echo '(';
            echo number_format($woi->e_quantity,2);
            echo '@';
            echo number_format($woi->e_rate,2);
            echo '/';
            echo $woi->e_unit;
            echo ')';

            $e_cost += ($woi->e_quantity * $woi->e_rate);
            $e_size += $woi->e_quantity;

            $e_cost_full += ($woi->e_quantity * $woi->e_rate);
            $e_size_full += $woi->e_quantity;

        }
        echo '</td>';

        // Actual Quantity Rate
        echo '<td class="c">' . $woi->a_quantity . '@' . $woi->a_rate . '/' . $woi->a_unit . '</td>';

        // Actual Sub-Total
        echo "<td class='r'>".number_format($woi->a_quantity * $woi->a_rate,2)."</td>";

        echo '</tr>';

        // Build Sums
        $a_size += $woi->a_quantity;
        $a_cost += ($woi->a_quantity * $woi->a_rate);

        $a_cost_full += ($woi->a_quantity * $woi->a_rate);
        $a_size_full += $woi->a_quantity;

        $x_kind = $woi->kind;
        $x_status = $woi->status;
        $x_invoice_id = $woi->invoice_id;

    }
    drawSummaryRow($e_size,$e_cost,$a_size,$a_cost);
    drawSummaryRow($e_size_full,$e_cost_full,null,null,'Estimate');
    drawSummaryRow(null,null,$a_size_full,$a_cost_full,'Actual');
    echo '</table>';
}

// History
// @todo would we very cool to have ajax loaded object histories
// $args = array(
// //    'ajax' => true,
//     'list' => $this->WorkOrder->getHistory(),
// );
// echo $this->partial('../elements/diff-list.phtml',$args);

function drawSummaryRow($e_size,$e_cost,$a_size,$a_cost,$name='Sub Total')
{
    echo '<tr>';
    echo '<td class="b" colspan="2">' . $name . ':</td>';

    // Estimated Data
    echo '<td class="r s">';
    if ($e_size) {
        echo '(';
        echo number_format($e_size,2);
        echo '@';
        echo number_format($e_cost,2);
        echo ')';
    }
    echo '</td>';

    if ($a_size) {
        echo '<td class="c"><strong>' . number_format($a_size,2) . '</strong></td>';
        echo '<td class="l">&curren;' . number_format($a_cost,2) . '</td>';
    }
    echo '</tr>';
}

?>

<script type="text/javascript">
$('#contact_name').autocomplete({
    source: '<?php echo $this->link('/contact/ajax') ?>',
    change: function(event, ui) { if (ui.item) { $('#contact_id').val(ui.item.id); } }
});
$('#wo_date').datepicker();
$('#requester').autocomplete({
    source: '<?php echo $this->link('/contact/ajax'); ?>',
    change: function(event, ui) {
        if (ui.item) {
            $('#account').val(ui.item.label);
            $('#account_id').val(ui.item.id);
        }
    }
});
$('#kind').autocomplete({ minLength:0, source:['Single','Project','Monthly','Quarterly','Yearly'] });
// $('#add_contact_name').autocomplete({
//     source:'<?php echo $this->link('/contact/ajax'); ?>',
//     change:function(event, ui) { if (ui.item) {  $("#add_contact_id").val(ui.item.id); $("#add_contact_name").val(ui.item.contact); } }
// });
</script>