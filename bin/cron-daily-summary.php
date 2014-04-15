#!/usr/bin/php -e
<?php
/**
	@file
	@brief Details the work done Yesterday

	@see http://stackoverflow.com/questions/4897215/php-mail-how-to-send-html
	@see http://php.net/manual/en/function.mail.php
	@see http://www.w3schools.com/php/func_mail_mail.asp
*/

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');
$date = strftime('%Y-%m-%d',$time);

// Pending Work
$html = '<html><head><title>[Imperium] Daily Summary Processor: ' . $date . '</title></head>';
$html.= '<body>';
$html.= "<h2>Pending WorkOrder Items</h2>\n";

$sql = 'SELECT workorder_item.*,contact.name as contact_name ';
$sql.= '  FROM workorder_item';
$sql.= '  JOIN workorder ON workorder_item.workorder_id = workorder.id ';
$sql.= '  JOIN contact ON workorder.contact_id = contact.id ';
$sql.= '  WHERE workorder_item.status = ? ';
$sql.= '   AND workorder.status = ? '; 
$sql.= '  ORDER BY workorder_item.workorder_id';
$res = radix_db_sql::fetchAll($sql,array('Pending','Active'));

if (count($res)) {
    $html.= _draw_details($res);
} else {
    $html.= "<p>No Pending Work</p>\n";
}

// echo "Active Work:\n";
// _draw_details($res);

// Completed Work - From Yesterday
$html.= "<h2>Completed Work</h2>\n";

$date = strftime('%Y-%m-%d',$time - 86400);

$sql = 'SELECT workorder_item.*,contact.name as contact_name ';
$sql.= '  FROM workorder_item';
$sql.= '  JOIN workorder ON workorder_item.workorder_id = workorder.id ';
$sql.= '  JOIN contact ON workorder.contact_id = contact.id ';
$sql.= '  WHERE workorder_item.date = ? AND workorder_item.status = ? ';
$sql.= '  ORDER BY workorder_item.workorder_id';
$res = radix_db_sql::fetchAll($sql,array($date,'Complete'));

if (count($res)) {
    $html.= _draw_details($res);
} else {
    $html.= "<p>No Completed Work</p>\n";
}
$html.= '</body>';
$html.= '</html>';

// Make New Message
// http://www.emailonacid.com/blog/details/C13/the_importance_of_content-type_character_encoding_in_html_emails
$head = array();
$head['Content-Transfer-Encoding'] = '8bit';
$head['Content-Type']    = 'text/html; charset="UTF-8"';
$head['From']            = sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']);
$head['MIME-Version']    = '1.0';
$head['Message-Id']      = md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST);
$head['Subject']         = '[Imperium] Daily Summary Processor: ' . $date;
$head['To']              = $_ENV['cron']['alert_to'];
$head['X-MailGenerator'] = 'Edoceo Imperium v2013.43';

$mail = null;
ksort($head);
foreach ($head as $k=>$v) {
	$mail.= "$k: $v\r\n";
}
$mail.= "\r\n";
$mail.= $html;
// print_r($mail);
send_mail($_ENV['cron']['alert_to'], $mail);

function _draw_details($res)
{
	$ret = null;
    $wox = null;
    $sum = 0;
    foreach ($res as $woi) {
    	$woi = (object)$woi;
        if ($wox != $woi->workorder_id) {
            if (!empty($sum)) {
                $ret.= '<p><strong>Sum:  ' . number_format($sum, 2) . "</strong></p>\n";
                $sum = 0;
            }
            $ret.= "<h3>WorkOrder #{$woi->workorder_id} ({$woi->contact_name})</h3>\n";
        }

        if (!empty($woi->a_quantity)) {
            $ret.= "<p>{$woi->a_quantity} @ {$woi->a_rate}/{$woi->a_unit} - {$woi->name}</p>\n";
            $sum += ($woi->a_quantity * $woi->a_rate);
        } else {
            $ret.= "<p>{$woi->e_quantity} @ {$woi->e_rate}/{$woi->e_unit} - {$woi->name}</p>\n";
            $sum += ($woi->e_quantity * $woi->e_rate);
        }

        $wox = $woi->workorder_id;

    }
    if (!empty($sum)) {
        $ret.= '<p><strong>Sum:  ' . number_format($sum, 2) . "</strong></p>\n";
        $sum = 0;
    }
    
    return $ret;
}
