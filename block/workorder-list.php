<?php
/**
    @file
    @brief  Work Order List Element
            Draws the standard table of Work Orders

    @package Edoceo Imperium
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;

if (empty($data) || empty($data['list'])) {
	return(0);
}
if (!is_array($data['list'])) {
	return(0);
}

$bill_total = $open_total = 0;

// @todo This is fucking ugly!
// @todo Remove this shitty hack!
$cols = array('star','id','date','status','kind','contact','note','bill_amount','open_amount');

echo '<table>';
// List each Invoice Item
foreach ($data['list'] as $item) {

    echo '<tr class="rero ' . strtolower($item['status']) . ' ' . strtolower($item['kind']) . '">';

    // Star
    echo '<td>' . star($item['star']) . '</td>';
    // ID
    echo '<td><a href="' . Radix::link('/workorder/view?w=' . $item['id']) . '">#' . $item['id'] . '</td>';
    // Printable Link
    if (in_array('print', $cols)) {
		echo '<td><a href="' . Radix::link('/workorder/pdf?w=' . $item['id']) . '">' . img('/tango/22x22/devices/printer.png','Get PDF') . '</a></td>';
	}

    if ( (in_array('date',$cols)) && (isset($item['date'])) ) {
        echo '<td class="c">' . ImperiumView::niceDate($item['date']) . '</td>';
    }
    echo '<td>' . $item['kind'] . '/' . $item['status'] . '</td>';

    if (isset($item['contact_name'])) {
        if (in_array('contact',$cols)) {
            echo '<td><a href="' . Radix::link('/contact/view?c=' . $item['contact_id']) . '">' . $item['contact_name'] . '</td>';
        }
    }
    if (in_array('note',$cols)) {
        echo '<td>';
        $x = min(max(strpos($item['note'],"\n"),32),64);
        echo trim(substr($item['note'],0,$x));
        echo '</td>';
    }
    if (in_array('open_amount',$cols)) {
        echo '<td class="bill" title="Open Amount">' . number_format($item['open_amount'],2) . '</td>';
    }
    if (in_array('bill_amount',$cols)) {
        echo '<td class="bill" title="Bill Amount">(' . number_format($item['bill_amount'],2) . ')</td>';
    }

    echo '</tr>';

    $open_total+= $item['open_amount'];
    $bill_total+= $item['bill_amount'];
}

echo '<tr class="ro">';
echo '<td class="b" colspan="' . (count($cols)-3) . '">Total:</td>';
echo '<td class="b r">' . number_format($open_total,2) . '</td>';
echo '<td class="b r">' . number_format($bill_total,2) . '</td>';
echo '</tr>';
echo '</table>';
