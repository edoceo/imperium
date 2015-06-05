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

$_ENV['title'] = array('Invoice','#' .$this->Invoice['id']);

App::addMRU(Radix::link('/invoice/view?i=' . $this->Invoice['id']));

$contact_address_list = array();

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

echo '<form action="' . Radix::link('/invoice/save?i=' . $this->Invoice['id']) . '" method="post">';
// echo '<div style="float:right;">';
// echo star($this->Invoice['star'] ? $this->Invoice['star'] : 'star_' );
// echo '</div>';

echo '<table>';
echo '<tr>';
// Contact
if (empty($this->Contact['id'])) {
    echo '<td class="l">Contact:</td>';
    echo '<td><input id="contact_name" name="contact_name" type="text" />';
    echo '</td>';
} else {
    echo '<td class="l">Contact:</td>';
    echo '<td><a href="' . Radix::link('/contact/view?c='.$this->Contact['id']) . '">' . $this->Contact['name'] . '</a>';
    echo '</td>';
}

// Date & Due Information
echo '<td class="l">Date:</td><td>';
echo Form::date('date',$this->Invoice['date'], array('id'=>'iv_date'));
if ($this->Invoice['due_diff'] < 0) {
    echo '&nbsp;<span class="s">Due in ' . abs($this->Invoice['due_diff']) . ' days</span>';
} else {
    if ($this->Invoice['status'] != 'Paid') {
        echo '&nbsp;<span class="s">Past Due ' . abs($this->Invoice['due_diff']) . ' days</span>';
    }
}
echo '</td></tr>';

// Bill & Ship Address
$list = array();
$list[] = '-None-';
if (is_array($this->ContactAddressList)) {
    $list+= $this->ContactAddressList;
}

$input = Form::select('bill_address_id', $this->Invoice['bill_address_id'], $list,null);
echo '<tr>';
echo '<td class="b r">Bill To:</td><td>' . $input . '</td>';

$input = Form::select('ship_address_id', $this->Invoice['ship_address_id'], $list,null);
echo '<td class="b r">Ship To:</td><td>' . $input . '</td>';
echo '</tr>';

echo "<tr><td class='b r'>Note:</td><td colspan='3'>" . Form::textarea('note',$this->Invoice['note'],array('style'=>'height:3em;width:90%;')) . '</td></tr>';
echo '<tr><td class="l">Bill Total:</td><td class="l">' . number_format($this->Invoice['bill_amount'],2)."</td></tr>";
echo '<tr><td class="l">Paid Total:</td><td class="l"';
if ($this->Invoice['paid_amount'] < $this->Invoice['bill_amount']) {
    echo ' style="color:#f00;"';
}
echo '>' . number_format($this->Invoice['paid_amount'], 2) . '</td></tr>';

// Status
echo '<tr>';
echo '<td class="l">Status:</td><td>' . Form::select('status', $this->Invoice['status'], $this->StatusList) . '</td>';
echo '</tr>';

echo '</table>';

// Buttons
echo '<div class="cmd">';
echo Form::hidden('contact_id',$this->Invoice['contact_id']);
echo '<input class="good" name="a" type="submit" value="Save">';

// Hawk Monitoring?
if ($this->Invoice->hasFlag(Invoice::FLAG_HAWK)) {
    echo '<input name="a" type="submit" value="No Hawk">';
} else {
    if ($this->Invoice->canHawk()) {
        echo '<input name="a" type="submit" value="Hawk">';
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
            		echo '<input class="fail" name="a" type="submit" value="' . $x . '">';
            		break;
            	default:
					echo '<input class="exec" name="a" type="submit" value="' . $x . '">';
				}
            }
        }
    }
}
echo '</div>';
echo '</form>';

// Invoice Notes
if (!empty($this->Invoice['id'])) {

    $url = Radix::link('/note/create?i=' . $this->Invoice['id']);
    $arg = array(
        'list' => $this->InvoiceNoteList,
        'page' => $url,
    );
    echo Radix::block('note-list',$arg);
}

// Invoice Items
// $base = Zend_Controller_Front::getInstance()->getBaseUrl();
$item_total = 0;
$item_tax_total = 0;
//$link = Radix::link('/invoice/item');

echo '<h2><i class="fa fa-list"></i> Invoice Items ';
echo '<span class="s">[ <a class="fancybox fancybox.ajax" href="' . Radix::link('/invoice/item?i=' . $this->Invoice['id']) . '">';
echo img('/tango/24x24/actions/list-add.png','Add Item');
echo '</a> ]</span>';
echo '</h2>';

// Item Location

if ((isset($this->InvoiceItemList)) && (is_array($this->InvoiceItemList)) && (count($this->InvoiceItemList) > 0)) {

    echo '<div id="item-list">';
    echo '<table style="margin:0px;width:100%;">';
    echo '<tr><th>Description</th><th>Quantity</th><th>Rate</th><th>Cost</th><th>Tax</th></tr>';
    // <th>Tax</th>
    foreach ($this->InvoiceItemList as $ivi) {
        $item_subtotal = $ivi['rate'] * $ivi['quantity'];
        $item_total += $item_subtotal;
        if ($ivi['tax_rate'] > 0) {
            $item_tax_total+= round($item_subtotal * $ivi['tax_rate'],2);
        }

        echo '<tr class="rero">';
        echo '<td class="b"><a class="fancybox fancybox.ajax" href="' . Radix::link('/invoice/item?id=' . $ivi['id']) . '">' .$ivi['name'] . '</a></td>';
        echo '<td class="c b">' .number_format($ivi['quantity'],2) . '</td>';
        echo '<td class="r">' . number_format($ivi['rate'],2) . '/' . $ivi['unit'] . '</td>';
        echo '<td class="r">' . number_format($item_subtotal, 2) . '</td>';
        if ($ivi['tax_rate'] > 0) {
            echo '<td class="r"><sup>' . tax_rate_format($ivi['tax_rate']) . '</sup></td>';
        } else {
            echo '<td class="r">&mdash;</td><td></td>';
        }
        echo '</tr>';
    }
    echo '<tr><td class="b" colspan="3">Sub-Total:</td><td class="l">' . number_format($item_total,2) . '</td></tr>';
    echo '<tr><td class="b" colspan="3">Tax Total:</td><td class="l">' . number_format($item_tax_total,2) . '</td></tr>';
    echo '<tr><td class="b" colspan="3">Bill Total:</td><td class="l">&curren;' . number_format($item_total + $item_tax_total, 2) . '</td></tr>';
    echo '<tr><td class="b" colspan="3">Paid Total:</td><td class="l">&curren;' . number_format($this->Invoice['paid_amount'], 2) . '</td></tr>';
    echo '<tr><td class="b" colspan="3">Balance:</td><td class="l" style="color: #f00;">&curren;' . number_format($item_total + $item_tax_total - $this->Invoice['paid_amount'], 2) . '</td></tr>';
    echo '</table>';
    echo '</div>';
}

// Transactions
if ( count($this->InvoiceTransactionList) > 0) {

    $sum = 0;

    echo '<h2 style="clear:both;"><i class="fa fa-money"></i> Transactions</h2>';
    echo '<table>';
    echo '<tr><th>Date</th><th>Account / Note</th><th>Debit</th><th>Credit</th></tr>';

    foreach ($this->InvoiceTransactionList as $le) {

        $sum+= $le->amount;

        $link = Radix::link('/account/transaction?id=' . $le->account_journal_id, ImperiumView::niceDate($le->date));

        echo '<tr>';
        echo '<td class="c">' . $link . '</td>';

        $link = Radix::link('/account/journal?id=' . $le->account_id, $le->account_name);
        echo '<td>' . $link;
        if (strlen($le->note)) {
            echo '/'.$le->note;
        }
        echo '</td>';
        // todo: debit/credit columns
        if ($le->amount < 0) {
            echo "<td class='r'>&curren;".number_format(abs($le->amount),2)."</td><td>&nbsp;</td>";
        } else {
            echo "<td>&nbsp;</td><td class='r'>&curren;".number_format($le->amount,2)."</td>";
        }
        echo '</tr>';
    }
    echo '<tr class="ro">';
	echo '<td class="b" colspan="2">Amount Due:</td>';
	echo '<td><td class="b r">&curren;' . number_format($sum,2) . '</td>';
	echo '</tr>';
    echo '</table>';
}

// History
$args = array('list' => $this->Invoice->getHistory());
echo Radix::block('diff-list',$args);
