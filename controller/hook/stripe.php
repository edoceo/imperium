<?php
/**
    @file
    @brief Stripe Checkout Event Listener

    We Need to Upgrade
    https://stripe.com/docs/upgrades?since=2011-11-17#api-changelog
*/

// _api_log_request();

$buf = file_get_contents('php://input');
$obj = json_decode($buf, true);


$fh = fopen('/tmp/stripe-callback.log', 'a');
fwrite($fh, "Stripe Request\n");
fwrite($fh, print_r($_SERVER, true));
// fwrite($fh, print_r($_GET, true)); // Empty
// fwrite($fh, print_r($_POST, true)); // Empty
fwrite($fh, $buf); // JSON
fwrite($fh, print_r($obj, true)); // PHP Array
fclose($fh);

header('Content-Type: application/json; charset=utf-8');

die(json_encode(array(
	'status' => 'success',
)));

// Event account.updated
/*
{
  "id": "evt_6NnNLiG1jU0000",
  "created": 1433675848,
  "livemode": true,
  "type": "account.updated",
  "data": {
    "object": {
      "id": "dRHXQBBMDl2MWodMNsdsrEzk1pI4cEeA",
      "email": "your@account.tld",
      "statement_descriptor": "YOUR COMPANY",
      "display_name": "Your Company",
      "timezone": "Etc/UTC",
      "details_submitted": true,
      "currencies_supported": [
        "usd",
        "aed",
        "afn",
        "all",
        "amd",
        "ang",
        "aoa",
        "ars",
        "aud",
        "awg",
        "azn",
        "bam",
        "bbd",
        "bdt",
        "bgn",
        "bif",
        "bmd",
        "bnd",
        "bob",
        "brl",
        "bsd",
        "bwp",
        "bzd",
        "cad",
        "cdf",
        "chf",
        "clp",
        "cny",
        "cop",
        "crc",
        "cve",
        "czk",
        "djf",
        "dkk",
        "dop",
        "dzd",
        "eek",
        "egp",
        "etb",
        "eur",
        "fjd",
        "fkp",
        "gbp",
        "gel",
        "gip",
        "gmd",
        "gnf",
        "gtq",
        "gyd",
        "hkd",
        "hnl",
        "hrk",
        "htg",
        "huf",
        "idr",
        "ils",
        "inr",
        "isk",
        "jmd",
        "jpy",
        "kes",
        "kgs",
        "khr",
        "kmf",
        "krw",
        "kyd",
        "kzt",
        "lak",
        "lbp",
        "lkr",
        "lrd",
        "lsl",
        "ltl",
        "lvl",
        "mad",
        "mdl",
        "mga",
        "mkd",
        "mnt",
        "mop",
        "mro",
        "mur",
        "mvr",
        "mwk",
        "mxn",
        "myr",
        "mzn",
        "nad",
        "ngn",
        "nio",
        "nok",
        "npr",
        "nzd",
        "pab",
        "pen",
        "pgk",
        "php",
        "pkr",
        "pln",
        "pyg",
        "qar",
        "ron",
        "rsd",
        "rub",
        "rwf",
        "sar",
        "sbd",
        "scr",
        "sek",
        "sgd",
        "shp",
        "sll",
        "sos",
        "srd",
        "std",
        "svc",
        "szl",
        "thb",
        "tjs",
        "top",
        "try",
        "ttd",
        "twd",
        "tzs",
        "uah",
        "ugx",
        "uyu",
        "uzs",
        "vnd",
        "vuv",
        "wst",
        "xaf",
        "xcd",
        "xof",
        "xpf",
        "yer",
        "zar",
        "zmw"
      ],
      "default_currency": "usd",
      "country": "US",
      "object": "account",
      "business_name": "Your Company, Inc.",
      "business_url": "your-company.com",
      "transfer_enabled": true,
      "charge_enabled": false,
      "phone_number": "2062826500"
    },
    "previous_attributes": {
      "charge_enabled": true
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": null,
  "api_version": "2011-11-17"
}
*/

// Event: customer.deleted
/*
{
  "id": "evt_6P5uweqiwW0000",
  "created": 1433975436,
  "livemode": false,
  "type": "customer.deleted",
  "data": {
    "object": {
      "object": "customer",
      "created": 1420925444,
      "id": "cus_000qKDTKjftgrZ",
      "livemode": false,
      "email": "customer@example.com",
      "metadata": {
      },
      "subscriptions": {
        "object": "list",
        "total_count": 0,
        "has_more": false,
        "url": "/v1/customers/cus_000qKDTKjftgrZ/subscriptions",
        "data": [

        ],
        "count": 0
      },
      "account_balance": 0,
      "currency": "usd",
      "cards": {
        "object": "list",
        "total_count": 1,
        "has_more": false,
        "url": "/v1/customers/cus_000qKDTKjftgrZ/cards",
        "data": [
          {
            "id": "card_999qG2B6DuPIkq",
            "object": "card",
            "last4": "4242",
            "brand": "Visa",
            "funding": "credit",
            "exp_month": 12,
            "exp_year": 2016,
            "fingerprint": "999ksrwEK7X8KNZn",
            "country": "US",
            "name": "customer@example.com",
            "address_city": null,
            "address_zip": "12345",
            "cvc_check": "pass",
            "address_zip_check": "pass",
            "dynamic_last4": null,
            "metadata": {
            },
            "customer": "cus_000qKDTKjft999",
            "type": "Visa"
          }
        ],
        "count": 1
      },
      "default_card": "card_999qG2B6DuPIkq",
      "sources": {
        "object": "list",
        "total_count": 1,
        "has_more": false,
        "url": "/v1/customers/cus_000qKDTKjftgrZ/sources",
        "data": [
          {
            "id": "card_999qG2B6DuPIkq",
            "object": "card",
            "last4": "4242",
            "brand": "Visa",
            "funding": "credit",
            "exp_month": 12,
            "exp_year": 2016,
            "fingerprint": "999ksrwEK7X8KNZn",
            "country": "US",
            "name": "customer@example.com",
            "address_city": null,
            "address_zip": "12345",
            "cvc_check": "pass",
            "address_zip_check": "pass",
            "dynamic_last4": null,
            "metadata": {
            },
            "customer": "cus_000qKDTKjftgrZ",
            "type": "Visa"
          }
        ],
        "count": 1
      },
      "default_source": "card_999qG2B6DuPIkq",
      "active_card": {
        "id": "card_999qG2B6DuPIkq",
        "object": "card",
        "last4": "4242",
        "brand": "Visa",
        "funding": "credit",
        "exp_month": 12,
        "exp_year": 2016,
        "fingerprint": "999ksrwEK7X8KNZn",
        "country": "US",
        "name": "customer@example.com",
        "address_city": null,
        "address_zip": "12345",
        "cvc_check": "pass",
        "address_zip_check": "pass",
        "dynamic_last4": null,
        "metadata": {
        },
        "customer": "cus_000qKDTKjftgrZ",
        "type": "Visa"
      }
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": "iar_999u2ltRRM0000",
  "api_version": "2011-11-17"
}
*/

// Event: customer.subscription.deleted
/*
{
  "id": "evt_6P5upoDkcP0000",
  "created": 1433975436,
  "livemode": false,
  "type": "customer.subscription.deleted",
  "data": {
    "object": {
      "id": "sub_5UVqn1TiE79999",
      "plan": {
        "interval": "month",
        "name": "seo-10",
        "created": 1348967743,
        "amount": 999,
        "currency": "usd",
        "id": "seo-10",
        "object": "plan",
        "livemode": false,
        "interval_count": 1,
        "metadata": {
        },
        "statement_descriptor": null,
        "statement_description": null
      },
      "object": "subscription",
      "start": 1420925444,
      "status": "canceled",
      "customer": "cus_000qKDTKjf0000",
      "current_period_start": 1433971844,
      "current_period_end": 1436563844,
      "ended_at": 1433975435,
      "canceled_at": 1433975435,
      "quantity": 1,
      "application_fee_percent": null,
      "discount": null,
      "tax_percent": null,
      "metadata": {
      }
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": "iar_6P5u2ltRRM0000",
  "api_version": "2011-11-17"
}
*/

// Event: customer.subscription.updated
/*
{
  "id": "evt_6P4yqFqEK10000",
  "created": 1433971965,
  "livemode": false,
  "type": "customer.subscription.updated",
  "data": {
    "object": {
      "id": "sub_5UVikzql9R0000",
      "plan": {
        "interval": "month",
        "name": "seo-10",
        "created": 1348967743,
        "amount": 999,
        "currency": "usd",
        "id": "seo-10",
        "object": "plan",
        "livemode": false,
        "interval_count": 1,
        "metadata": {
        },
        "statement_descriptor": null,
        "statement_description": null
      },
      "object": "subscription",
      "start": 1420924970,
      "status": "active",
      "customer": "cus_000ijQ1xmK0000",
      "current_period_start": 1433971370,
      "current_period_end": 1436563370,
      "quantity": 1,
      "application_fee_percent": null,
      "discount": null,
      "tax_percent": null,
      "metadata": {
      }
    },
    "previous_attributes": {
      "current_period_start": 1431292970,
      "current_period_end": 1433971370
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": null,
  "api_version": "2011-11-17"
}
*/

// Event: invoice.created
/*
{
  "id": "evt_6P4ypiabuu0000",
  "created": 1433971964,
  "livemode": false,
  "type": "invoice.created",
  "data": {
    "object": {
      "date": 1433971964,
      "id": "in_6P4yQ7hziQ0000",
      "period_start": 1431292970,
      "period_end": 1433971370,
      "lines": {
        "subscriptions": [
          {
            "id": "sub_5UVikzql9R0000",
            "object": "line_item",
            "type": "subscription",
            "livemode": false,
            "amount": 999,
            "currency": "usd",
            "proration": false,
            "period": {
              "start": 1433971370,
              "end": 1436563370
            },
            "subscription": null,
            "quantity": 1,
            "plan": {
              "interval": "month",
              "name": "seo-10",
              "created": 1348967743,
              "amount": 999,
              "currency": "usd",
              "id": "seo-10",
              "object": "plan",
              "livemode": false,
              "interval_count": 1,
              "metadata": {
              },
              "statement_descriptor": null,
              "statement_description": null
            },
            "description": null,
            "discountable": true,
            "metadata": {
            }
          }
        ]
      },
      "subtotal": 999,
      "total": 999,
      "customer": "cus_000ijQ1xmK0000",
      "object": "invoice",
      "attempted": false,
      "closed": false,
      "forgiven": false,
      "paid": false,
      "livemode": false,
      "attempt_count": 0,
      "amount_due": 999,
      "currency": "usd",
      "starting_balance": 0,
      "ending_balance": null,
      "next_payment_attempt": 1433975564,
      "webhooks_delivered_at": null,
      "application_fee": null,
      "subscription": "sub_5UVikzql9R0000",
      "tax_percent": null,
      "tax": null,
      "metadata": {
      },
      "statement_descriptor": null,
      "description": null,
      "receipt_number": null,
      "statement_description": null
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": null,
  "api_version": "2011-11-17"
}
*/

// Event: invoice.updated
/*
{
  "id": "evt_6P5uomwchl0000",
  "created": 1433975436,
  "livemode": false,
  "type": "invoice.updated",
  "data": {
    "object": {
      "date": 1433972368,
      "id": "in_6P55w0vpXu0000",
      "period_start": 1431293444,
      "period_end": 1433971844,
      "lines": {
        "subscriptions": [
          {
            "id": "sub_5UVqn1TiE70000",
            "object": "line_item",
            "type": "subscription",
            "livemode": false,
            "amount": 999,
            "currency": "usd",
            "proration": false,
            "period": {
              "start": 1433971844,
              "end": 1436563844
            },
            "subscription": null,
            "quantity": 1,
            "plan": {
              "interval": "month",
              "name": "seo-10",
              "created": 1348967743,
              "amount": 999,
              "currency": "usd",
              "id": "seo-10",
              "object": "plan",
              "livemode": false,
              "interval_count": 1,
              "metadata": {
              },
              "statement_descriptor": null,
              "statement_description": null
            },
            "description": null,
            "discountable": true,
            "metadata": {
            }
          }
        ]
      },
      "subtotal": 999,
      "total": 999,
      "customer": "cus_000qKDTKjf0000",
      "object": "invoice",
      "attempted": false,
      "closed": true,
      "forgiven": false,
      "paid": false,
      "livemode": false,
      "attempt_count": 0,
      "amount_due": 999,
      "currency": "usd",
      "starting_balance": 0,
      "ending_balance": null,
      "next_payment_attempt": null,
      "webhooks_delivered_at": 1433972376,
      "application_fee": null,
      "subscription": "sub_5UVqn1TiE70000",
      "tax_percent": null,
      "tax": null,
      "metadata": {
      },
      "statement_descriptor": null,
      "description": null,
      "receipt_number": null,
      "statement_description": null
    },
    "previous_attributes": {
      "closed": false,
      "next_payment_attempt": 1433975968
    }
  },
  "object": "event",
  "pending_webhooks": 1,
  "request": "iar_6P5u2ltRRM0000",
  "api_version": "2011-11-17"
}
*/