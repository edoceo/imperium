<?php
/**
    @file
    @brief Invoice List Element - draws the standard table of invoices

    @package Imperium
*/

$bill_total = 0;
$paid_total = 0;
$date_skip_list = array('Paid','Void');

// Include Paginator Stuffs?
echo '<table>';
if (isset($paginator))
{
?>
<tr>
<th><?php echo $paginator->sort('ID', 'id');?></th>
<th><?php echo $paginator->sort('Date', 'date');?></th>
<th><?php echo $paginator->sort('Status', 'status');?></th>
<th><?php echo $paginator->sort('Contact/Company', 'contact_name');?></th>
<th><?php echo $paginator->sort('Bill', 'bill_amount');?></th>
<th><?php echo $paginator->sort('Paid', 'paid_amount');?></th>
<th><?php echo $paginator->sort('Due', 'due_diff');?></th>
</tr>
<?php
}

// List Items
foreach ($data['list'] as $x) {

    $item = new Invoice($x);

    echo '<tr class="rero ' . strtolower($item->status) . '">';

    // Star
    echo '<td>' . star($item->star) . '</td>';
    // ID
    echo '<td><a href="' . radix::link('/invoice/view?i='.$item->id) . '">#' . $item->id . '</a></td>';
    // Printable Link
    echo '<td><a href="' . radix::link('/invoice/pdf?i=' . $item->id) . '">' . img('/tango/22x22/devices/printer.png','Get PDF') . '</a></td>';

    echo '<td>' . $item->status . '</td>';

    echo '<td class="c">';
    echo ImperiumView::niceDate($item->date);
    // echo '<td class="r">';
    $h = $t = null;
    if  (!in_array($item->status,$date_skip_list)) {
        if ($item->due_diff <= 0) {
            $t = sprintf('Invoice is Due in %d days',abs($item->due_diff));
            $h = sprintf('%d Out',abs($item->due_diff));
        } else {
            $t = sprintf('Invoice is Past Due in %d days',abs($item->due_diff));
            $h = sprintf('%d Due',abs($item->due_diff));
        }
        echo sprintf(' <span class="s" title="%s">%s</span>',$t,$h);
    }
    echo '</td>';

    echo '<td>' . $item->kind . '</td>';
    // echo '<td>' . substr($item->note,0,strrpos($item->note,' ',min(72,strlen($item->note)))) . '</td>';
    $x = min(max(strpos($item->note,"\n"),32),64);
    echo '<td>' . trim(substr($item->note,0,$x)) . '</td>';
    //echo "<td>". $html->link($item['Contact']['name'],'/contacts/view/'.$item['Contact']['id']) . "</td>";
    if (isset($item->contact_name)) {
        echo '<td><a href="' . radix::link('/contact/view?c='.$item->contact_id) . '">' . html($item->contact_name) . '</a></td>';
    } else {
        echo '<td>&nbsp;</td>';
    }
    echo '<td class="bill">' . number_format($item->bill_amount,2) . '</td>';
    echo '<td class="paid">' . number_format($item->paid_amount,2) . '</td>';

    echo '</tr>';

    if ($item->status != 'Void') {
        $bill_total+= $item->bill_amount;
        $paid_total+= $item->paid_amount;
    }
}

// Total
echo '<tr class="ro">';
echo '<td class="b" colspan="8">Total:</td>';
echo '<td class="b r">' . number_format($bill_total,2) . '</td>';
echo '<td class="b r">' . number_format($paid_total,2) . '</td>';
echo '</tr>';

if ($bill_total != $paid_total) {
  echo '<tr class="ro">';
  echo '<td class="b" colspan="8">Balance:</td>';
  if ($bill_total > $paid_total) {
    echo '<td>&nbsp;</td><td class="b r">' . number_format($bill_total - $paid_total,2) . '</td>';
  } else {
    echo '<td class="b r">' . number_format($paid_total - $bill_total,2) . '</td><td>&nbsp;</td>';
  }
  echo '</tr>';
}

echo '</table>';
