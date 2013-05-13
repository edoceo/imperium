#!/usr/bin/php -e
<?php
/**
  @file
  @brief Generates the Monthy, Quarterly and Yearly Invoices
         This script should be run daily by cron
    @note can't decide between command line args and/or forcing ENV vars
*/

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');

echo 'Imperium WorkOrder Subscription Processor: ' . $date . "\n";

// Find Processable Subscriptions
$sql = 'SELECT * FROM workorder ';
$sql.= ' WHERE status = ? AND kind = ? AND date <= ? ';
$sql.= ' ORDER BY id ASC ';

// Monthly Subscription
// Triggers with current Day of Month == Work Order Day of Month 
$res = $db->fetchAll($sql,array('Active','Monthly',$date));
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = $span % 30;
    $wait = 30 - $diff;
    echo "Monthly: $wo->id span:$span; diff:$diff; wait:$wait;\n";
    if ($diff == 0) {
        wo2iv($wo->id);
        echo "** Need to Send It\n";
    }
}

// Quarterly Subscription
// Triggers when: Day == Work Order Day of Month + 90
$res = $db->fetchAll($sql,array('Active','Quarterly',$date));
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 90);
    $wait = 90 - $diff;
    echo "Quarterly: #$wo->id span:$span; diff:$diff; wait:$wait;\n";
    if ($diff == 0) {
        wo2iv($wo->id);
        echo "** Need to Send It\n";
    }
}

// Yearly Subscription
$res = $db->fetchAll($sql,array('Active','Yearly',$date));
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 365);
    $wait = 365 - $diff;
    echo "Yearly: #$wo->id span:$span; diff:$diff; wait:$wait;\n";
    if ($diff == 0) {
        $iv = wo2iv($wo->id);
        if ($iv->id) {
            // Send
            echo "** Need to Send It\n";
        }
    }
}

/**
    @todo Should be Part of WorkOrder Class
*/
function wo2iv($id)
{
    $wo = new WorkOrder($id);
    $iv = $wo->toInvoice();

    echo "wo2iv: WorkOrder #{$wo->id} => Invoice #{$iv->id}\n";
    
    return $iv;

}
