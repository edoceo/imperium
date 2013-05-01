#!/usr/bin/php -e
<?php
/**
  @file
  @brief Generates the Monthy, Quarterly and Yearly Invoices
         This script should be run daily by cron
    @note can't decide between command line args and/or forcing ENV vars
*/

// Configurations
$cli_opt = getopt('c:d:',array('config:','date:'));
if (empty($cli_opt['config']) && !empty($cli_opt['c'])) $cli_opt['config'] = $cli_opt['c'];
if (!empty($cli_opt['config'])) putenv('IMPERIUM_CONFIG=' . $cli_opt['config']);

// Bootstrapper
require_once(dirname(dirname(dirname(__FILE__))) . '/boot.php');
// require_once(dirname(dirname(dirname(__FILE__))) . '/approot/controllers/WorkorderController.php');

$auth = Zend_Auth::getInstance();
$db = Zend_Registry::get('db');

$time = mktime(0,0,0);
if (!empty($cli_opt['d'])) $cli_opt['date'] = $cli_opt['d'];
if (!empty($cli_opt['date'])) $time = strtotime($cli_opt['date']);
$date = strftime('%Y-%m-%d',$time);


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
    echo "Monthly: $wo->id span:$span diff:$diff \n";
    if ($diff == 0) {
        wo2iv($wo->id);
    }
}

// Quarterly Subscription
// Triggers when: Day == Work Order Day of Month + 90
$res = $db->fetchAll($sql,array('Active','Quarterly',$date));
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 90);
    echo "Quarterly: #$wo->id span:$span diff:$diff \n";
    if ($diff == 0) {
        wo2iv($wo->id);
    }
}

// Yearly Subscription
$res = $db->fetchAll($sql,array('Active','Yearly',$date));
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 365);
    echo "Yearly: #$wo->id span:$span diff:$diff \n";
    if ($diff == 0) {
        wo2iv($wo->id);
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

}
