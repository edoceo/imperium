#!/usr/bin/php -e
<?php
/**
  @file
  @brief Sends the Hawk reminder every time it's run.
         Should be run every day
         Sends notice for invoices at 0 days, 10 days and ($date % 30 == 0)
*/

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');

// if (empty($cli_opt['diff'])) $cli_opt['diff'] = 10;
if (empty($cli_opt['span'])) $cli_opt['span'] = 10;

echo 'Imperium Invoice Hawk Processor: ' . strftime('%Y-%m-%d',$time) . " for {$cli_opt['span']} days\n";

// Find Hawkable Invoices
$sql = 'SELECT *,extract(days from current_timestamp - date) as days FROM invoice ';
$sql.= ' WHERE ';
if ( (!empty($argv[1])) && (intval($argv[1]) > 0) ) {
    $sql.= sprintf(' id = %d',intval($argv[1]));
} else {
    // If Date Supplied Then Find Invoices That Are %d Days old on That Day
    $sql.= ' ((date::date - current_timestamp::date) %% ? = 0) ';
    // $sql.= ' ( ( (date::date - current_timestamp::date) > ? ) AND ( (date::date - current_timestamp::date) %% ? = 0) ) ';
    // $sql.= " ( date < current_timestamp - '%d days'::interval) ";
    // $sql.= ' AND (flag & %d = %d) ';
    // $sql.= " AND ( status NOT IN ('Void','Active') ) ";
    // $sql.= " AND status in ('Hawk','Sent') ";
    // $sql.= ' AND (bill_amount > 0 AND (paid_amount is null OR paid_amount < bill_amount)) ';
}
// $sql.= ' GROUP BY contact_id ';
$order = ' ORDER BY contact_id, id ASC ';

$sql = sprintf($sql,$_ENV['invoice']['hawk_days_min']); // ,Invoice::FLAG_HAWK,Invoice::FLAG_HAWK);
// echo "$sql\n";

// $res = $db->fetchAll($sql,array(strftime('%Y-%m-%d',$time),array('Active')));
$res = $db->fetchAll("$sql AND status in ('Active')",array($cli_opt['span']));
echo "You have " . count($res) . " Active Invoices to POST\n";
echo "\n";

// ,'Sent'
// $res = $db->fetchAll("$sql AND status in ('Sent')",array($cli_opt['diff']));
// echo "You have " . count($res) . " Sent Invoices\n";
// echo "\n";

$res = $db->fetchAll("$sql AND status IN ('Hawk','Sent') $order",array($cli_opt['diff']));
echo "You have " . count($res) . " Active Invoices Past Due\n";

foreach ($res as $rec) {

    $iv = new Invoice($rec->id);
    $co = new Contact($iv->contact_id);
    echo trim("Invoice #{$iv->id} {$iv->date} ({$iv->status} - {$rec->days}) to {$co->name}#{$iv->contact_id} {$co->email} ");
    if (empty($co->email)) {
        echo " * Fail No Email!\n";
        continue;
    }

    // Mark Invoice to Hawk Status
    switch ($iv->status) {
    case 'Active':
    	break;
    case 'Sent':
    	echo " Aged, Sent\n";
    	echo "  {$_ENV['application']['base']}/invoice/view?i={$iv->id}\n";
    	continue;
    }

    // $ah = Auth_Hash::make($iv);

    // Make New Message
    $mail = _new_mail();
    if (!empty($_ENV['cron']['mailtest'])) {
        $mail->addTo($_ENV['cron']['mailtest']);
    } else {
        $mail->addTo($co->email);
    }

    // $EmailComposeMessage = new stdClass();
    // $EmailComposeMessage->to = $co->email;
    // $EmailComposeMessage->RecipientList = array();
    // $EmailComposeMessage->RecipientList[''] = '- none -';
    // $EmailComposeMessage->RecipientList+= $co->getEmailList();

    $mail->setSubject('Reminder: Invoice #' . $iv->id . ' from ' . $_ENV['company']['name']);

    $body = null;
    $file = APP_ROOT . '/approot/etc/invoice-hawk.txt';
    if (!is_file($file)) {
        die("Cannot Hawk Invoices need invoice-hawk.txt\n");
    }
    $body = file_get_contents($file);

    // Substitutions
    $body = str_replace('$id',$iv->id,$body);
    $body = str_replace('$date',strftime($_ENV['format']['nice_date'],strtotime($iv->date)),$body);
    $body = str_replace('$days',$rec->days,$body);
    $body = str_replace('$name',$co->contact,$body);
    $body = str_replace('$contact_name',$co->contact,$body);
    $body = str_replace('$invoice_link',"{$_ENV['application']['base']}/checkout/invoice/hash/{$iv->hash}",$body);
    $body = str_replace('$app_company',$_ENV['company']['name'],$body);

    // Include Work Orders
    // $list = $Invoice->getWorkOrders();
    // foreach ($list as $wo) {
    //     $wo = new WorkOrder($wo);
    //     $ah = Auth_Hash::make($wo);
    //     $ss->EmailComposeMessage->body.= 'Work Order #' . $wo->id . "\n";
    //     $ss->EmailComposeMessage->body.= '  ' . AppTool::baseUri() . '/hash/' . $ah['hash'] . "\n";
    // }

    // From Text Input
    // $list = trim($_POST['to_list']);
    // foreach (explode(',',$list) as $x) {
    //     $mail->addTo($x);
    // }

    // Add Text
    $mail->setBodyText($body,null,Zend_Mime::ENCODING_7BIT);

    /*
    // Add a Part?
    $part = new Zend_Mime_Part($req->getPost('text'));
    $part->type = 'text/plain';
    $part->encoding = Zend_Mime::ENCODING_7BIT;
    $part->disposition = Zend_Mime::DISPOSITION_INLINE;
    $mail->addAttachment($part);
    */
    // Add PDF
    /*
    $pdf = new InvoicePDF($iv->id);
    $part = new Zend_Mime_Part($pdf->render());
    $part->filename = 'Invoice-' . $iv->id . '.pdf';
    $part->type = 'application/pdf; name="' . $part->filename . '"';
    $part->encoding = Zend_Mime::ENCODING_BASE64;
    $part->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
    $mail->addAttachment($part);
    */
    //$mail->createAttachment($pdf->render(),'application/pdf',Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,$part->filename);

    // $hawk_summary.= "$note\n";

    // Send the Message
    if ($cli_opt['send']) {
        echo " - Sending";
		$uri = parse_url($_ENV['mail']['smtp']);
		$smtp = new Zend_Mail_Transport_Smtp($uri['host'],array(
			'auth' => 'login',
			'username' => $uri['user'],
			'password' => $uri['pass'],
			'ssl'  => 'tls',
			'port' => $uri['port'],
		));
        $mail->send($smtp);
        sleep(2); // Crappy Throttle
    }
    echo "\n";
}
