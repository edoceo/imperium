#!/usr/bin/php -e
<?php
/**
  @file
  @brief Details the work done Yesterday
*/

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');

$date = strftime('%Y-%m-%d',$time);
echo 'Imperium Daily Summary Processor: ' . $date . "\n";

// Pending Work
$sql = 'SELECT workorder_item.*,contact.name as contact_name ';
$sql.= '  FROM workorder_item';
$sql.= '  JOIN workorder ON workorder_item.workorder_id = workorder.id ';
$sql.= '  JOIN contact ON workorder.contact_id = contact.id ';
$sql.= '  WHERE workorder_item.status = ? ';
$sql.= '   AND workorder.status = ? '; 
$sql.= '  ORDER BY workorder_item.workorder_id';
$res = $db->fetchAll($sql,array('Pending','Active'));

echo "## Pending Work:\n";
if (count($res)) {
    _draw_details($res);
} else {
    echo "  No Pending Work\n";
}
echo "\n";

// echo "Active Work:\n";
// _draw_details($res);

// Completed Work - From Yesterday
$date = strftime('%Y-%m-%d',$time - 86400);

$sql = 'SELECT workorder_item.*,contact.name as contact_name ';
$sql.= '  FROM workorder_item';
$sql.= '  JOIN workorder ON workorder_item.workorder_id = workorder.id ';
$sql.= '  JOIN contact ON workorder.contact_id = contact.id ';
$sql.= '  WHERE workorder_item.date = ? AND workorder_item.status = ? ';
$sql.= '  ORDER BY workorder_item.workorder_id';
$res = $db->fetchAll($sql,array($date,'Complete'));

if (count($res)) {
    echo "## Completed:\n";
    _draw_details($res);
} else {
    echo "  No Completed Work\n";
}


function _draw_details($res)
{
    $wox = null;
    $sum = 0;
    foreach ($res as $woi) {
        if ($wox != $woi->workorder_id) {
            if (!empty($sum)) {
                echo '  Sum:  ' . number_format($sum) . "\n";
                $sum = 0;
            }
            echo "WorkOrder #{$woi->workorder_id} ({$woi->contact_name})\n";
        }

        if (!empty($woi->a_quantity)) {
            echo "  {$woi->a_quantity} @ {$woi->a_rate}/{$woi->a_unit} - {$woi->name}\n";
            $sum += ($woi->a_quantity * $woi->a_rate);
        } else {
            echo "  {$woi->e_quantity} @ {$woi->e_rate}/{$woi->e_unit} - {$woi->name}\n";
            $sum += ($woi->e_quantity * $woi->e_rate);
        }

        $wox = $woi->workorder_id;

    }
    if (!empty($sum)) {
        echo '  Sum:  ' . number_format($sum) . "\n";
        $sum = 0;
    }
}
