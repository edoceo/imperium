<?php
/**
	@file
	@brief PayPal Instant Payment Notification

	@see https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-ipn-notify
*/

_api_log_request();

// // read the post from PayPal system and add 'cmd'
// $req = 'cmd=' . urlencode('_notify-validate');
//  
// foreach ($_POST as $key => $value) {
// 	$value = urlencode(stripslashes($value));
// 	$req .= "&$key=$value";
// }

$post = $_POST;
$post['cmd'] = '_notify-validate';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
$res = curl_exec($ch);
curl_close($ch);

fwrite($fh,"\nPayPal Say:\n$res\n---\n");

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
 
 
if (strcmp ($res, "VERIFIED") == 0) {
	// check the payment_status is Completed
	// check that txn_id has not been previously processed
	// check that receiver_email is your Primary PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment
}
else if (strcmp ($res, "INVALID") == 0) {
	// log for manual investigation
}


fclose($fh);


/* Another version */

/**
    @file
    @brief PayPal IPN Event Listener
*/

$fh = fopen('/tmp/paypal-ipn.log','a');
echo "\nAt: " . strftime('%Y-%m-%d %H:%M:%S') . "\n";
if (count($_GET)) {
    echo "GET:\n";
    fwrite($fh,print_r($_POST,true));
}
if (count($_POST)) {
    echo "POST:\n";
    fwrite($fh,print_r($_POST,true));
}

// // read the post from PayPal system and add 'cmd'
// $req = 'cmd=' . urlencode('_notify-validate');
// foreach ($_POST as $key => $value) {
// 	$value = urlencode(stripslashes($value));
// 	$req .= "&$key=$value";
// }

$post = $_POST;
$post['cmd'] = '_notify-validate';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
$res = curl_exec($ch);
curl_close($ch);

fwrite($fh,"\nPayPal Say:\n$res\n---\n");

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
 
 
if (strcmp ($res, "VERIFIED") == 0) {
	// check the payment_status is Completed
	// check that txn_id has not been previously processed
	// check that receiver_email is your Primary PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment
}
else if (strcmp ($res, "INVALID") == 0) {
	// log for manual investigation
}


fclose($fh);

/*
Array
(
    [mc_gross] => 240.00
    [protection_eligibility] => Eligible
    [address_status] => confirmed
    [item_number1] => 
    [payer_id] => 9ZQ2XEBR3WTM4
    [tax] => 0.00
    [address_street] => 6220 116th Ave NE
    [payment_date] => 23:48:12 Dec 22, 2013 PST
    [payment_status] => Completed
    [charset] => windows-1252
    [address_zip] => 98033
    [mc_shipping] => 0.00
    [mc_handling] => 0.00
    [first_name] => Nikhilesh
    [mc_fee] => 7.26
    [address_country_code] => US
    [address_name] => Diffen LLC
    [notify_version] => 3.7
    [custom] => 
    [payer_status] => verified
    [business] => paypal@edoceo.com
    [address_country] => United States
    [num_cart_items] => 1
    [mc_handling1] => 0.00
    [address_city] => Kirkland
    [verify_sign] => AU6hVSNsK4ffpuY5-DZ2r9OP9zbkAqLEGBSAHtf9KSSXR1XxReR7972u
    [payer_email] => jasuja@diffen.com
    [mc_shipping1] => 0.00
    [tax1] => 0.00
    [txn_id] => 90K6478825830960N
    [payment_type] => instant
    [payer_business_name] => Diffen LLC
    [last_name] => Jasuja
    [address_state] => WA
    [item_name1] => Invoice #1219
    [receiver_email] => paypal@edoceo.com
    [payment_fee] => 7.26
    [quantity1] => 1
    [receiver_id] => 7XP3UV647TEMJ
    [txn_type] => cart
    [mc_gross_1] => 240.00
    [mc_currency] => USD
    [residence_country] => US
    [transaction_subject] => Shopping CartInvoice #1219
    [payment_gross] => 240.00
    [ipn_track_id] => 8227917a89df6
)
*/
