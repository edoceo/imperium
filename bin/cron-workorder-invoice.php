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

$dim = intval(date('t')); // Days in Month
$dom = intval(date('j')); // Day of Month

ob_start();

// Find Processable Subscriptions
$sql = 'SELECT * FROM workorder ';
$sql.= ' WHERE status = ? AND kind = ? AND date <= ? ';
$sql.= ' ORDER BY id ASC ';

// Monthly Subscription
// Triggers with current Day of Month == Work Order Day of Month 
$res = $db->fetchAll($sql,array('Active','Monthly',$date));
echo "WO: " . count($res) . " Monthly Subscriptions\n";
foreach ($res as $wo) {
    $wod = intval(date('j',strtotime($wo->date)));
    echo "WO: #{$wo->id} Triggers on $wod";
    if ($wod >= $dim) {
        echo " (skew $wod to $dim)";
        $wod = $dim;
    }
    if ($wod == $dom) {
        // echo "Monthly: $wo->id span:$span; diff:$diff; wait:$wait;\n";
        if ($diff == 0) {
            if (wo2iv($wo->id)) {
                echo " ** Need to Send It";
            }
        }
    }
    echo "\n";
}
echo "\n";

// Quarterly Subscription
// Triggers when: Day == Work Order Day of Month + 90
$res = $db->fetchAll($sql,array('Active','Quarterly',$date));
echo 'WO ' . count($res) . " Quarterly Subscriptions\n";
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 90);
    $wait = 90 - $diff;
    echo "Quarterly: #$wo->id span:$span; diff:$diff; wait:$wait;";
    if ($diff == 0) {
        wo2iv($wo->id);
        echo " ** Need to Send It";
    }
    echo "\n";
}
echo "\n";

// Yearly Subscription
$res = $db->fetchAll($sql,array('Active','Yearly',$date));
echo 'WO ' . count($res) . " Yearly Subscriptions\n";
foreach ($res as $wo) {
    $span = floor(($time - strtotime($wo->date)) / 86400);
    $diff = ($span % 365);
    $wait = 365 - $diff;
    if ($diff == 0) {
        echo "Yearly: #$wo->id span:$span; diff:$diff; wait:$wait;";
        $iv = wo2iv($wo->id);
        if ($iv->id) {
            // Send
            echo " ** Need to Send It";
        }
    }
    echo "\n";
}

$body = ob_get_clean();

send_mail_summary($body);

/**
    Send the Mail Summary
*/
function send_mail_summary($body)
{
    global $date, $dom, $dim;

    $uri = parse_url($_ENV['mail']['smtp']);
    $smtp = new Zend_Mail_Transport_Smtp($uri['host'],array(
        'auth' => 'login',
        'username' => $uri['user'],
        'password' => $uri['pass'],
        'ssl'  => 'tls',
        'port' => $uri['port'],
    ));

    $mail = new Zend_Mail();
    $mail->setFrom($uri['user']);
    $mail->setSubject('Imperium WorkOrder Subscription Processor: ' . $date . " ($dom/$dim)");
    $mail->addTo($_ENV['cron']['notifyto']);
    $mail->addHeader('X-MailGenerator','Edoceo Imperium');
    $mail->setBodyText($body,null,Zend_Mime::ENCODING_7BIT);
    $mail->send($smtp);
}


/**
    @todo Should be Part of WorkOrder Class
*/
function wo2iv($id)
{
    $wo = new WorkOrder($id);
    try {
        $iv = $wo->toInvoice();
    } catch (Exception $e) {
        echo "EE " . $e . "\n";
        return false;
    }

    echo "WO #{$wo->id} => IV #{$iv->id} for ${$iv->bill_amount}\n";

    // Post to their Account
    $C = new Contact($iv->contact_id);

    // Generate a Transaction to Post to This Clients Account Receivable
    $aje = new AccountJournalEntry();
    $aje->date = $iv->date;
    $aje->note = 'Charge for Invoice #' . $iv->id;
    $aje->save();

    // Debit Accounts Receivable for this Client
    $ale = new AccountLedgerEntry();
    $ale->account_id = $_ENV['account']['receive_account_id'];
    $ale->account_journal_id = $aje->id;
    $ale->amount = abs($iv->bill_amount) * -1;
    $ale->link_to = ImperiumBase::getObjectType('contact');
    $ale->link_id = $iv->contact_id;
    $ale->save();

    // Credit Customer Account - or Revenue for Instant Revenue?
    // Old Query, Why from account by contact?
    $ale = new AccountLedgerEntry();
    if ($C->account_id) {
        $ale->account_id = $C->account_id;
    } else {
        $ale->account_id = $_ENV['account']['revenue_account_id'];
    }
    $ale->account_journal_id = $aje->id;
    $ale->amount = abs($iv->bill_amount);
    $ale->link_to = ImperiumBase::getObjectType($iv);
    $ale->link_id = $iv->id;
    $ale->save();
    echo "IV Posted {$aje->note} " . number_format($ale->amount,2) . "\n";

    // Send The Invoice via Email

    return $iv;

}
