<?php
/**
 * Work Order View
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Displays the main details of the Work Order and allows Edit
 *
 * @copyright  2008 Edoceo, Inc
 * @package    edoceo-imperium
 * @link       http://imperium.edoceo.com
 * @since      File available since Release 1013
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

use Edoceo\Imperium\UI\Button;

// Jump List
if (count($this->jump_list)) {
    echo '<div class="jump_list">';
    $list = array();
    foreach ($this->jump_list as $x) {
        if ($x == $this->WorkOrder['id']) {
            $list[] = '<span class="hi">#' . $x . '</span>';
        } elseif ($x < $this->WorkOrder['id'] ) {
            $list[] = '<a href="' . Radix::link('/workorder/view?w=' . $x) . '">&laquo; #' . $x . '</a>';
        } else {
            $list[] = '<a href="' . Radix::link('/workorder/view?w=' . $x) . '">#' . $x . ' &raquo;</a>';
        }
    }
    echo implode(' | ',$list);
    echo '</div>';
}

echo '<form action="' . Radix::link('/workorder/save?w=' . $this->WorkOrder['id']) . '" method="post">';

?>

<div class="row">
    <div class="col-md-6">
        <div class="input-group mb-2">
            <div class="input-group-text">Contact:</div>
            <?php
            if (empty($this->Contact['id'])) {
                echo '<input class="form-control" id="contact_name" name="contact_name" type="text">';
            } else {
                echo '<input class="form-control" readonly value="' . __h($this->Contact['name']) . '">';
                // echo '<br /><i class="fas fa-phone"></i> ' . html($this->Contact['phone']) . '</td>';
            }
            ?>
            <a class="btn btn-outline-secondary" href="/contact/view?c=<?= $this->Contact['id'] ?>"><i class="fa-regular fa-user"></i></a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group mb-2">
            <div class="input-group-text">Requester:</div>
            <?= Form::text('requester', $this->WorkOrder['requester'], [ 'class' => 'form-control' ]) ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="input-group mb-2">
            <div class="input-group-text">Date:</div>
            <?= Form::date('date', $this->WorkOrder['date'], [ 'class' => 'form-control'] ) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-group mb-2">
            <div class="input-group-text">Rate:</div>
            <?= Form::number('base_rate',$this->WorkOrder['base_rate'], [ 'class'=>'form-control rate' ]); ?>
            <?= Form::select('base_unit', $this->WorkOrder['base_unit'], Base_Unit::getList(), [ 'class'=>'form-select rate' ]) ?>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <?= Form::select('kind', $this->WorkOrder['kind'], $this->KindList, [ 'class' => 'form-select']) ?>
    </div>
    <div class="col-md-3">
        <div class="input-group mb-2">
            <div class="input-group-text">Status:</div>
            <input class="form-control" readonly value="<?= $this->WorkOrder['status'] ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-m-12 mb-2">
        <textarea class="form-control" name="note"><?= __h($this->WorkOrder['note']) ?></textarea>
    </div>
</div>

<div class="col-md-6">Bill Total: <span class=""><?= number_format($this->WorkOrder['bill_amount'], 2) ?></div>
<div class="col-md-6">Open Total: <span class="text-danger"><?= number_format($this->WorkOrder['open_amount'], 2) ?></div>

<?php

//echo star($this->WorkOrder['star'] ? $this->WorkOrder['star'] : 'star_' );

// Hidden Fields & Buttons
echo '<div class="cmd">';
echo Form::hidden('id',$this->WorkOrder['id']);
echo Form::hidden('contact_id',$this->WorkOrder['contact_id']);
echo Button::save();
echo Button::print(Radix::link(sprintf('/workorder/pdf?w=%s', $this->WorkOrder['id'])));

    // echo '<li><a href="' . Radix::link('/workorder/view?w=' . $_ENV['workorder']['id']) . '">&laquo; Work Order #' . $this->WorkOrder->id . '</a></li>';

    // Add Item
    // echo '<li><a class="ajax-edit" data-name="woi-edit" href="' . Radix::link('/workorder/item?w=' . $this->WorkOrder->id) . '"><i class="far fa-plus-square"></i> Add Item</a></li>';

    //$menu1[] = array('/service_orders/post_payment','<i class="fas fa-money-check-alt"></i> Post Payment');
    //$menu1[] = array('/workorder/invoice', '<i class="fas fa-file-invoice-dollar"></i> Build Invoice');

    // echo '<li><hr /></li>';
    // $menu1[] = array("javascript:\$('#EmailSend').submit();", '<i class="fas fa-share-square"></i> Send');

    //$menu1[] = array('/service_orders/history', '<i class="fas fa-history"></i> History');


if (!empty($_ENV['workorder.workflow'])) {
    $list = array();
    foreach ($_ENV['workorder.workflow'] as $k=>$v) {
        if ( ($k == '*') || ($k == $this->WorkOrder['status']) ) {
            $list = explode(',',$v);
            foreach ($list as $x) {
            	switch ($x) {
            	case 'Delete':
            	case 'Void':
            		echo '<input class="btn btn-danger me-2" name="a" type="submit" value="' . trim($x) . '" />';
            		break;
            	default:
					echo '<input class="btn btn-secondary me-2" name="a" type="submit" value="' . trim($x) . '" />';
				}
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
// echo '<table class="table"><tr>';
// echo '<td class="l">Contact</td><td><input id="add_contact_name" name="contact" type="text"></td>';
// echo '<td><input id="add_contact_id" name="add_contact_id" type="hidden" value=""><input name="c" type="submit" value="Add"></td>';
// echo '</tr></table>';
// echo '</form>';

// Work Order Notes
if (!empty($this->WorkOrder['id'])) {

    $url = $this->link('/note/create?w=' . $this->WorkOrder['id']);
    $arg = array(
        'list' => $this->WorkOrderNoteList,
        'page' => $url,
    );
    echo Radix::block('note-list', $arg);
}

// Work Order Files
if (!empty($this->WorkOrder['id'])) {

    // $url = $this->link('/file/create?w=' . $this->WorkOrder['id']);
    // $arg = array(
    //     'list' => $this->WorkOrderFileList,
    //     'page' => $url,
    // );
    echo Radix::block('file-list', $this->WorkOrderFileList); // $arg);
}

// Work Order Items
if (!empty($this->WorkOrder['id'])) {
    ?>

    <section class="mt-4">
    <div class="d-flex justify-content-between">
        <div><h2 id="woi-list">Work Order Items</h2></div>
        <div>
            <a accesskey="n" class="btn btn-secondary ajax-edit" data-name="woi-edit" href="<?= Radix::link('/workorder/item?w=' . $this->WorkOrder['id']) ?>">
                Add Item <i class="far fa-plus-square"></i>
            </a>
        </div>
    </div>

    <div id="woi-edit"></div>

    </section>

    <?php
}

if (count($this->WorkOrderItemList) > 0) {

    $a_size = $e_size = $e_size_full = 0;
    $a_cost = $e_cost = $e_cost_full = 0;

    $x_kind = null;
    $x_status = null;

    echo '<table class="table">';
    // echo '<tr><th>Quantity</th><th>Name</th><th>Rate</th><th>Status</th><th>Subtotal</th></tr>';
    // @todo Need to track Invoice Totals
    foreach ($this->WorkOrderItemList as $woi) {

        // Split by Invoice, Kind or Status
        // Summary Row
        if ( ($x_kind != $woi['kind']) || ($x_status != $woi['status']) ) {

            drawSummaryRow($e_size,$e_cost,$a_size,$a_cost);
            // The method of tying WOI => IVI is incorrect
            //  We should tie IVI => WOI, so that here we'd look for IVI where workorder_item_id = ?
            //if ($woi->invoice_id) {
            //    $text = $this->link("/invoice/view?i={$woi->invoice_id}","Invoice #{$woi->invoice_id}");
            //} else {
                $text =  $woi['status'] . ' ' . $woi['kind'] . ' Items';
            //}
            echo '<tr><th colspan="6">' . $text . '</th></tr>';

            // Reset Counters
            $a_cost = $e_cost = 0;
            $a_size = $e_size = 0;

        }

        // $name = (isset($woi->date) ? date('m/d/y ',strtotime($woi->date)) : null) . $woi->name;
        $link = '<a class="ajax-edit" data-name="woi-edit" href="' . Radix::link('/workorder/item?id=' . $woi['id']) . '">%s</a>';

        echo '<tr>';

        // Date
        echo '<td>';
        if (!empty($woi['date'])) {
            $html = null;
            $time = strtotime($woi['date']);
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
        echo '<td class="b">' . sprintf($link,$woi['name']) . '</td>';

        // Estimated Values?
        echo '<td class="r s">';
        if ( (!empty($woi['e_quantity'])) && (floatval($woi['e_quantity']) > 0) ) {
            echo '(';
            echo number_format($woi['e_quantity'],2);
            echo '@';
            echo number_format($woi['e_rate'],2);
            echo '/';
            echo $woi['e_unit'];
            echo ')';

            $e_cost += ($woi['e_quantity'] * $woi['e_rate']);
            $e_size += $woi['e_quantity'];

            $e_cost_full += ($woi['e_quantity'] * $woi['e_rate']);
            $e_size_full += $woi['e_quantity'];

        }
        echo '</td>';

        // Actual Quantity Rate
        echo '<td class="c">' . $woi['a_quantity'] . '@' . $woi['a_rate'] . '/' . $woi['a_unit'] . '</td>';

        // Actual Sub-Total
        echo "<td class='r'>".number_format($woi['a_quantity'] * $woi['a_rate'], 2)."</td>";

        echo '</tr>';

        // Build Sums
        $a_size += $woi['a_quantity'];
        $a_cost += ($woi['a_quantity'] * $woi['a_rate']);

        $a_cost_full += ($woi['a_quantity'] * $woi['a_rate']);
        $a_size_full += $woi['a_quantity'];

        $x_kind = $woi['kind'];
        $x_status = $woi['status'];

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

?>

<?php
ob_start();
?>
<script>
$(function() {
	WorkOrder.initForm();
});
</script>
<?php
Layout::addScript(ob_get_clean());


/**
 *
 */
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
