<?php
/**
	Designed to Import CSVs from PayPal Reports	
	Type 2 are the reports from https://business.paypal.com/merchantdata/reportHome
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

class Account_Reconcile_PayPal_v2
{
	/**
		Parse PayPal Transaction Data
	*/
	static function parse($file)
	{
		$ret = array();

		if (!is_file($file)) {
			return $ret;
		}

		setlocale(LC_ALL, 'en_US.UTF-8');

		$fh = fopen($file,'r');
		if (empty($fh)) {
			return $ret;
		}

		//$bom = fread($fh, 3);
		//die("bom:$bom\n");

		$map = array(
			'Date',
			'Time',
			'Time Zone',
			'Description',
			'Currency',
			'Gross',
			'Fee',
			'Net',
			'Balance',
			'Transaction ID',
			'From Email Address',
			'Name',
			'Bank Name',
			'Bank Account',
			'Shipping and Handling Amount',
			'Sales Tax',
			'Invoice ID',
			'Reference Txn ID'
		);

		while ($csv = fgetcsv($fh, 4096, ',')) {

			// Skip first Row if Header
			if ($csv[0] == 'Date') {
				continue;
			}

			// Ledger Entry for Paypal Deposit (Gross)
			if (count($csv) < 18) {
				continue;
			}

			$csv = array_slice($csv, 0, count($map));
			$csv = array_combine($map, $csv);

			$csv['Gross'] = floatval(preg_replace('/[^\d\.\-]/',null,$csv['Gross']));
			$csv['Fee'] = floatval(preg_replace('/[^\d\.\-]/',null,$csv['Fee']));
			$csv['Net'] = floatval(preg_replace('/[^\d\.\-]/',null,$csv['Net']));

			Radix::dump($csv);

			// Only Transactions with Fees Count
			if ( (empty($csv['Gross'])) && (empty($csv['Fee'])) ) {
				continue;
			}

			$le = new \stdClass();
			$le->date = $csv['Date'];
			$le->note = trim(sprintf('%s #%s: %s <%s>',
				$csv['Description'],
				$csv['Transaction ID'],
				$csv['Name'],
				$csv['From Email Address']
			));

			$le->amount = $csv['Gross'];
			$le->account_id = null;

			switch (trim($csv['Description'])) {
			//case 'eBay Payment Received':
			//case 'Payment Received':
			//case 'Shopping Cart Payment Received':
			case 'Subscription Payment':
			case 'Website Payment':

				if ($le->amount > 0) {

					// Someone Paying Me
					// Ledger Entry for Paypal Deposit
					$le->dr = $le->amount;
					$ret[] = $le;

					// Ledger Entry for Paypal Fee
					$le2 = new \stdClass();
					$le2->date = $csv['Date'];
					$le2->amount = $csv['Fee'];
					// $le2->account_id = 78; // Expense: PayPal Fees
					$le2->note = 'Fee for Transaction #' . $csv['Transaction'] . '';
					$le2->cr = abs($le2->amount);
					$ret[] = $le2;

				} else {

					// Im Paying Someone
					// Ledger Entry for Paypal Deposit
					$le->cr = abs($le->amount);
					//Radix::dump($csv);
					$ret[] = $le;

				}

				break;

			// Money Leaves PayPal to Expense
			//case 'eBay Payment Sent':
			case 'Express Checkout Payment':
			case 'General Payment':
			//case 'Payment Sent':
			//case 'Shopping Cart Payment Sent':
			//case 'Web Accept Payment Sent':

				// Debit to Checking
				//if (floatval($le->amount) < 0) {
				$le->dr = abs($le->amount);
				$ret[] = $le;

				break;
			//case 'Add Funds from a Bank Account': // Happens before Update to ...
			//case 'Order': // Requested Money From Us, Paid on *Sent
			//case 'Pending Balance Payment':
			//	continue 2; // Ignore
			//	break;

			//case 'Refund':
			case 'Payment Refund':

				$le->dr = abs($le->amount);
				$ret[] = $le;

				break;

			//case 'Update to Add Funds from a Bank Account': // Money Into Paypal from Bank
			//	// Debit to Checking
			//	$le->amount = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
			//	$le->cr = abs($le->amount);
			//	$le->account_id = 1;
			//	break;
			//case 'Withdraw Funds to a Bank Account':
			case 'General Withdrawal':
			case 'General Withdrawal - Bank Account':
				// Debit to Checking
				$le->cr = abs($le->amount);
				$ret[] = $le;
				break;
			case 'Reversal of General Account Hold':
			//	// What to do here?
			//	echo "Reversal of General Account Hold NOT HANDLED\n";
				Radix::dump($csv);
				break;
			default:
				// print_r($csv);
				//throw new \Exception("Cannot Handle Type: '{$csv[4]}'");
				echo "Cannot Handle Type: '{$csv['Description']}'<br>";
			}

			$stat[ $csv['Description'] ]++;

		}

		return $ret;
	}
}
