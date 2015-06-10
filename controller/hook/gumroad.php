<?php
/**
	Payment Notification Hook for Gumroad
	Webhook for Gumroad

	@see https://gumroad.com/settings/developer
	@see https://gumroad.com/ping
	@see https://gumroad.com/webhooks

*/

if (empty($_GET['salt'])) {
	radix::bail(400);
}

_api_log_request();

/*
* order_number
* seller_id
* product_id
* product_permalink
* email (the email of the buyer)
* full_name (if present, the name of the buyer)
* price (the price paid, in USD cents)
* variants (if present, an array of each variant choice: ['blue', 'small'])
* offer_code (if present)
* test (if you are buying your own product, for testing purposes)
* custom_fields (if present, a dictionary {'name' : 'john smith', 'spouse name' : 'jane smith'})
* shipping_information (if present, a dictionary)
* is_recurring_charge (if relevant, a boolean)
* is_preorder_authorization (if relevant, a boolean)
* revenue_share_amount_cents (if releva
*/


// If it's a Webhook Request?
/*
* email (the email of the buyer)
* full_name (if present, the name of the buyer)
* price (the price paid, in USD cents)
* variants (if present, an array of each variant choice: ['blue', 'small'])
* offer_code (if present)
* test (if you are buying your own product, for testing purposes)
* custom fields (if present, a dictionary {'name' : 'john smith', 'spouse name' : 'jane smith'})
* shipping information (if present, a dictionary)
*/

// Return should be a URL
$ret = $_ENV['application']['base'];

// $uri = magic_product_lookup();
header('Content-Type: text/plain');
die($ret);