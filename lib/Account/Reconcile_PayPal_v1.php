<?php
/**
 * PayPal Imports
 * Type 1 is from the Activity Download here:
 * The Fields are customizible, and the defaults change frequently.
 * https://business.paypal.com/merchantdata/reportHome
 * https://business.paypal.com/merchantdata/dlog
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;

class Account_Reconcile_PayPal_v1
{
	public static function parse($file)
	{
		$ret = array();

		if (!is_file($file)) {
			return $ret;
		}

		setlocale(LC_ALL, 'en_US.UTF-8');

		$fh = fopen($file, 'r');
		if (empty($fh)) {
			return $ret;
		}

		$bom = fread($fh, 3);
		$bom = bin2hex($bom);
		if ('efbbbf' == $bom) {
			// It's a UTF-8
		} else {
			// Rewind
			fseek($fh, 0, SEEK_SET);
		}

		$buf = fread($fh, 128);
		fseek($fh, -128, SEEK_CUR);

		$c_t = substr_count($buf, "\t");
		$c_c = substr_count($buf, ',');
		if (($c_c >= 1) && ($c_t == 0)) {
			$sep = ',';
		} elseif (($c_c == 0) && ($c_t >= 1)) {
			$sep = "\t";
		} else {
			throw new Exception('ARP#049: Cannot Discover Delimiter');
		}

		$map = fgetcsv($fh, 4096, $sep);
		//$map = array();
		//foreach ($row as $i => $k) {
		//	$map[$k] = $i;
		//}

		while ($csv = fgetcsv($fh, 4096, $sep)) {

			// Ledger Entry for Paypal Deposit (Gross)
			if (count($csv) < 11) {
				continue;
			}

			$csv = array_slice($csv, 0, count($map));
			$csv = array_combine($map, $csv);

			$csv['Type'] = trim($csv['Type']);
			$csv['Status'] = strtoupper($csv['Status']);
			$csv['Gross'] = floatval(preg_replace('/[^\d\.\-]/',null, $csv['Gross']));
			$csv['Fee'] = floatval(preg_replace('/[^\d\.\-]/',null, $csv['Fee']));
			$csv['Net'] = floatval(preg_replace('/[^\d\.\-]/',null, $csv['Net']));
			$csv['Sales Tax'] = floatval(preg_replace('/[^\d\.\-]/', null, $csv['Sales Tax']));

			// Only Process Completed Transactions
			switch ($csv['Status']) {
				case 'Denied':
				case 'Pending':
					continue 2;
			}

			// Only Transactions with Fees Count
			if ( (empty($csv['Gross'])) && (empty($csv['Fee'])) ) {
				continue;
			}

			$je = array(
				'date' => $csv['Date'],
				'note' => trim(sprintf('%s #%s: %s <%s> - %s #%s',
					$csv['Type'],
					$csv['Transaction ID'],
					$csv['Name'],
					$csv['From Email Address'],
					$csv['Item Title'],
					$csv['Item ID']
				)),
				'ledger_entry_list' => array(),
			);

			$le0 = array(
				'account_id' => null,
				'amount' => floatval($csv['Gross']),
			);

			$st = sprintf('%s/%s', $csv['Status'], $csv['Type']);
			switch ($st) {
			//case 'eBay Payment Received':
			//case 'Payment Received':
			//case 'Shopping Cart Payment Received':
			case 'COMPLETED/Bank Deposit to PP Account':
			case 'COMPLETED/Donation Payment':
			case 'COMPLETED/Express Checkout Payment':
			case 'COMPLETED/General Payment':
			case 'COMPLETED/Mass Pay Payment':
			case 'COMPLETED/Mobile Payment':
			case 'COMPLETED/Order':
			case 'COMPLETED/PreApproved Payment Bill User Payment':
			case 'COMPLETED/Reversal of General Account Hold':
			case 'COMPLETED/Subscription Payment':
			case 'COMPLETED/Website Payment':

				if ($le0['amount'] > 0) {

					// Incoming Money
					// Ledger Entry for Paypal Debit
					$le0['dr'] = $le0['amount'];
					$je['ledger_entry_list'][] = $le0;

					// If a Fee is Charged, it's a Negative Number
					if ($csv['Fee'] < 0) {
						// Ledger Entry for Paypal Fee
						$le2 = array(
							'cr' => abs($csv['Fee']),
							'amount' => $csv['Fee'],
							'note' => 'Fee for Transaction #' . $csv['Transaction ID'],
						);
						// $le2->account_id = 78; // Expense: PayPal Fees
						$je['ledger_entry_list'][] = $le2;
					}

					if ($csv['Sales Tax']) {

						// Ledger Entry for Sales Tax
						$le3 = array(
							'cr' => abs($csv['Sales Tax']),
							'amount' => $csv['Sales Tax'],
							'note' => 'Sales Tax Liability #' . $csv['Transaction ID'],
						);
						// $je['ledger_entry_list'][] = $le3;
					}

				} else {

					// Outgoing
					// Ledger Entry for Paypal Credit
					$le0 = array(
						'cr' => abs($le0['amount']),
					);
					$je['ledger_entry_list'][] = $le0;
				}

				break;

			//case 'Refund':
			case 'COMPLETED/Payment Refund':

				$le0['cr'] = abs($le0['amount']);
				$je['ledger_entry_list'][] = $le0;

				// Fee Refund
				if ($csv['Fee'] != 0) {
					// Ledger Entry for Paypal Fee
					$le2 = array(
						'dr' => abs($csv['Fee']),
						'amount' => $csv['Fee'],
						'note' => sprintf('PayPal #%s - Fee Reversal', $csv['Transaction ID']),
					);
					// $le2->account_id = 78; // Expense: PayPal Fees
					$je['ledger_entry_list'][] = $le2;
				}

				break;
			case 'COMPLETED/Payment Reversal':
				$le0['cr'] = abs($le0['amount']);
				$je['ledger_entry_list'][] = $le0;
				break;
			case 'COMPLETED/Chargeback Reversal':
				$le0['dr'] = abs($le0['amount']);
				$je['ledger_entry_list'][] = $le0;
				break;
			case 'COMPLETED/Dispute Fee':
				$le0['cr'] = abs($le0['amount']);
				$je['ledger_entry_list'][] = $le0;
				break;

			//case 'Withdraw Funds to a Bank Account':
			case 'COMPLETED/Auto-sweep':
			case 'COMPLETED/General Withdrawal':
			// case 'General Withdrawal/Pending': // Ignore
				// Transfer out of PayPal
				$le0['cr'] = abs($le0['amount']);
				$je['note'] = trim(sprintf('%s #%s',
					$csv['Type'],
					$csv['Transaction ID']
				));
				$je['ledger_entry_list'][] = $le0;
				break;

			case 'COMPLETED/Hold on Balance for Dispute Investigation':
				// var_dump($csv);
				$je['note'] = trim(sprintf('%s #%s <%s>',
					$csv['Type'],
					$csv['Transaction ID'],
					$csv['To Email Address'],
				));
				$le0['cr'] = abs($le0['amount']);

				$je['ledger_entry_list'][] = $le0;

				break;

			case 'COMPLETED/Cancellation of Hold for Dispute Resolution':
				// var_dump($csv);
				$le0['dr'] = abs($le0['amount']);
				$je['note'] = trim(sprintf('%s #%s <%s>',
					$csv['Type'],
					$csv['Transaction ID'],
					$csv['From Email Address'],
				));
				$je['ledger_entry_list'][] = $le0;

				break;

			case 'COMPLETED/Chargeback':

				$le0['cr'] = abs($le0['amount']);
				$je['note'] = trim(sprintf('%s #%s for #%s',
					$csv['Type'],
					$csv['Transaction ID'],
					$csv['Reference Txn ID'],
				));
				$je['ledger_entry_list'][] = $le0;

				break;

			case 'COMPLETED/Chargeback Fee':
				// var_dump($csv);
				$le0['cr'] = abs($le0['amount']);
				$je['note'] = trim(sprintf('%s #%s for #%s',
					$csv['Type'],
					$csv['Transaction ID'],
					$csv['Reference Txn ID'],
				));
				$je['ledger_entry_list'][] = $le0;
				break;
			case 'DENIED/Cancellation of Hold for Dispute Resolution':
				// PayPal Puts money BACK into our Account
				$le0['dr'] = abs($le0['amount']);
				$je['note'] = trim(sprintf('%s; %s; <%s> Original: %s',
					$csv['Type'],
					$csv['Status'],
					$csv['From Email Address'],
					$csv['Reference Txn ID'],
				));
				$je['ledger_entry_list'][] = $le0;
				break;
			case 'COMPLETED/General Authorization': // Followed by a Sale which affects balance
			case 'COMPLETED/General Currency Conversion': // There is a charge w/USD
			case 'PAID/Invoice Sent':
			case 'PENDING/Account Hold for Open Authorization':
			case 'PENDING/Bank Deposit to PP Account':
			case 'PENDING/General Authorization':
			case 'PENDING/Hold on Balance for Dispute Investigation':
				// Ignore
				break;
			default:
				var_dump($csv);
				//throw new \Exception("Cannot Handle Type: '{$csv[4]}'");
				echo "Cannot Handle Type: '{$st}'<br>";
			}

			if (!empty($je['ledger_entry_list'])) {
				$ret[] = $je;
			}

		}

		return $ret;
	}

}
