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

if (empty($cli_opt['span'])) $cli_opt['span'] = $_ENV['invoice']['hawk_days_min'];
if (!empty($cli_opt['diff'])) $cli_opt['span'] = $cli_opt['diff'];

$file = APP_ROOT . '/etc/invoice-hawk.txt';
if (!is_file($file)) {
	die("Cannot Hawk Invoices need etc/invoice-hawk.txt\n");
}

ob_start();
echo '<h1>Invoice Processor: ' . strftime('%Y-%m-%d',$time) . " for {$cli_opt['span']} days</h1>\n";

// Summary Data
$sql = 'SELECT count(id) FROM invoice ';
$sql.= ' WHERE status IN (\'Active\') ';
$sql.= ' AND (extract(days from current_timestamp - date) > ?) ';
$res = SQL::fetch_one($sql, array($cli_opt['span']));
echo "<p>" . $res . " Active Invoices to POST</p>\n";

$sql = 'SELECT count(id) FROM invoice ';
$sql.= ' WHERE status IN (\'Hawk\', \'Sent\') ';
$sql.= ' AND (extract(days from current_timestamp - date) > ?) ';
$res = SQL::fetch_one($sql, array($cli_opt['span']));
echo "<p>" . $res . " Sent Invoices over {$cli_opt['span']} days old</p>\n";


// Find Hawkable Invoices
$sql = 'SELECT *, extract(days from current_timestamp - date) as days FROM invoice ';
$sql.= ' WHERE status IN (\'Hawk\', \'Sent\') ';
// $sql.= ' AND ((extract(days from current_timestamp - date)::integer % ?) = 0) ';
$sql.= ' ORDER BY date, contact_id, id ASC ';
$res = SQL::fetch_all($sql); // , array($cli_opt['span']));

echo "<p>Past Due Invoices: " . count($res) . "</p>\n";

$sum = 0;
foreach ($res as $rec) {

	$iv = new Invoice($rec);
	$co = new Contact($rec['contact_id']);

	$bal = floatval($rec['bill_amount']) - floatval($rec['paid_amount']);

	echo "<p>Customer: {$co['name']} {$co['email']}</p>\n";
	echo "<p>Invoice #{$iv['id']}/{$iv['status']} \$$bal from {$iv['date']} ({$rec['days']} due)</p>\n";

	if (empty($co['email'])) {
		echo "<p style='color:#f00;font-weight:bold;'>FAIL: No Email for this Contact!</p>\n";
		continue;
	}

	// List of Invoices
	// $sql = 'SELECT * FROM invoice ';
	// $sql.= ' WHERE contact_id = ? AND status IN (\'Sent\') AND (extract(days from current_timestamp - date) >= ?) ';
	// $sql.= ' ORDER BY date';
	// $arg = array($co['id'], $cli_opt['span']);
	// $iv_res = SQL::fetch_all($sql, $arg);

	$mail = null;
	$mail = trim(file_get_contents($file));

	$mail = str_replace('%head_from%', sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']), $mail);
	$mail = str_replace('%head_hash%', md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST), $mail);
	// $mail = str_replace('%head_subj%', 'Reminder: Invoice Past Due Invoices', $mail);
	$mail = str_replace('%head_subj%', "Reminder: Invoice #{$iv['id']} from {$iv['date']} ({$rec['days']} days ago)", $mail);

	$mail = str_replace('%base_link%', $_ENV['application']['base'], $mail);

    $mail = str_replace('%mail_rcpt%', $co['email'], $mail);

    $mail = str_replace('%mail_sign%', $_ENV['company']['name'],$mail);

	// Substitutions
    $mail = str_replace('%contact_name%', $co['contact'], $mail);
    $mail = str_replace('%invoice_id%', $iv['id'], $mail);
    $mail = str_replace('%invoice_date%', strftime($_ENV['format']['nice_date'], strtotime($iv['date'])), $mail);
    $mail = str_replace('%invoice_age%', $rec['days'], $mail);
    // $mail = str_replace('%invoice_link%', "{$_ENV['application']['base']}/share?a={$iv['hash']}", $mail);
    $mail = str_replace('%invoice_link%', "{$_ENV['application']['base']}/checkout/invoice/hash/{$iv['hash']}", $mail);
    $mail = str_replace('%checkout_link%', "{$_ENV['application']['base']}/checkout?a={$iv['hash']}",$mail);

    // $mail = str_replace('$days', $rec->days, $mail);
    // $mail = str_replace('$name', $co->contact, $mail);

	// $mail.= '<p>Invoice <a href="' . $_ENV['application']['base'] . '/invoice/view?id=' . $rec['id'] . '">#' . $rec['id'] . '</a>';
	// $mail.= ' to <a href="' . $_ENV['application']['base'] . '/contact/view?id=' . $rec['contact_id'] . '">' . $co->name . '</a>';
	// $mail.= ' for ' . number_format($rec['bill_amount'], 2);
	// $mail.= ' is ' . $rec['days'] . ' days old</p>';

	if (preg_match('/\W?%\w+%\W?/', $mail, $m)) {
		print_r($m);
		die("<p style='color:#f00;font-weight:bold;'>WARN: Unprocessed Macros?</p>\n");
	}

    // if ($cli_opt['mail']) {
	// 	App_Mail::send($co['email'], $mail);
    // } else {
    // 	echo "<p>Skipped Mailing: {$co['email']}</p>\n";
    // }

    $sum += $bal;

}
echo "<p style='font-weight:700;'>Total Balance: $sum</p>\n";

// Send me the Summary

$body = ob_get_clean();


$mail = <<<EOF
Content-Transfer-Encoding: 8bit
Content-Type: text/html; charset="utf-8"
From: %head_from%
MIME-Version: 1.0
Message-Id: <%head_hash%>
Subject: %head_subj%
X-MailGenerator: Edoceo Imperium v2013.43

$body
EOF;

// Clean
$mail = str_replace('%head_from%', sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']), $mail);
$mail = str_replace('%head_hash%', md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST), $mail);
$mail = str_replace('%head_subj%', '[Imperium] Past Due Invoices Summary', $mail);
$mail = str_replace('%mail_rcpt%', $_ENV['cron']['alert_to'], $mail);

if (!empty($cli_opt['cron'])) {
	App_Mail::send($_ENV['cron']['alert_to'], $mail);
}
// if ( !empty($cli_opt['mail']) && !empty($_ENV['cron']['alert_to']) ) {
//
// } else {
// 	echo strip_tags($body);
// }
