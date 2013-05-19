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
$sql.= "   AND workorder.status IN (?) "; 
$sql.= '  ORDER BY workorder_item.workorder_id';
$res = $db->fetchAll($sql,array('Pending','Active'));

echo "Pending Work:\n";
_draw_details($res);


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

echo "Completed:\n";
_draw_details($res);

function _draw_details($res)
{
    $pre = null;
    $sum = 0;
    foreach ($res as $woi) {
        if ($pre != $woi->workorder_id) {
            if (!empty($sum)) {
                echo "  Sum:  $sum\n";
            }
            echo "WorkOrder #{$woi->workorder_id} ({$woi->contact_name})\n";
        }
        echo "  {$woi->a_quantity} @ {$woi->a_rate}/{$woi->a_unit} - {$woi->name}\n";
        $pre = $woi->workorder_id;
        $sum += ($woi->a_rate * $woi->a_quantity);
    }
    if (!empty($sum)) {
        echo "  Sum: $sum\n";
    }
}
