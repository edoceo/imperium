<?php
/**
	Account Reconciliation Tools
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

class Account_Reconcile
{
	/**
		List of supported formats
	*/
	public static $format_list = array(
		'chase' => 'Chase Credit Cards',
		'csvwfb' => 'Wells Fargo Comma Seperated',
		'square' => 'SquareUp Transaction CSV',
		'paypal' => 'Paypal CSV',
		'becu' => 'BECU CSV',
		// 'iq2005' => 'Intuit Quicken 2005 or newer',
		// 'qb2000' => 'Quickbooks 2000 or newer',
		// 'mm2002' => 'Microsoft Money 2002 or newer',
	);

	/**
		Parse a Number
	*/
	private static function _filter_number($x)
	{
		$r = floatval(preg_replace('/[^\-\d\.]+/', null, $x));
		return $r;
	}

	/**
		Parse the Data to Accounts
	*/
	static function parse($opt)
	{
		$ret = array();

		// Read the Line in the Format
		switch ($opt['kind']) {
		case 'becu':
			$imp = new \Edoceo\Imperium\Account\Import\BECU($opt['file'], $_POST['upload_id']);
			$ret = $imp->parse();
			uasort($ret, array('Edoceo\Imperium\Account_Reconcile', '_sortCallback'));
			break;
		case 'chase':
			$imp = new \Edoceo\Imperium\Account\Import\Chase($opt['file'], $_POST['upload_id']);
			$ret = $imp->parse();
			uasort($ret, array('Edoceo\Imperium\Account_Reconcile', '_sortCallback'));
			break;
		case 'csvwfb': // Wells Fargo CSV Format
			$ret = self::_parseWellsFargo($opt['file']);
			uasort($ret, array('Edoceo\Imperium\Account_Reconcile', '_sortCallback'));
			break;
		case 'paypal':
			$ret = self::_parsePayPal_v1($opt['file']);
			break;
		case 'qfx': // Quicken 2004 Web Connect
			//echo "<pre>".htmlspecialchars($buf)."</pre>";
			if (!preg_match('/^OFXHEADER:100/',$bf->data)) {
				trigger_error('Not a valid QFX file',E_USER_ERROR);
			}
			  if (preg_match_all("/^<STMTTRN>\n<TRNTYPE>(CHECK|CREDIT|DEBIT|DEP|DIRECTDEBIT|FEE|POS)\n<DTPOSTED>(\d{8})\n<TRNAMT>([\d\-\.]+)\n<FITID>(\d+)\n<NAME>(.+)<\/STMTTRN>\n/m",$bf->data,$m)) {
				$c_entries = count($m[0]);
				$trn_types = $m[1];
				$trn_dates = $m[2];
				$trn_amnts = $m[3];
				$trn_fitid = $m[4];
				$trn_names = $m[5];
				// echo "<pre>".print_r($trn_names,true)."</pre>";
				for ($i=0;$i<$c_entries;$i++)
				{
				  $je = new stdClass();
				  $je->id = null;
				  $je->ok = false;
				  $je->index = $i;
				  $je->date = substr($trn_dates[$i],4,2).'/'.substr($trn_dates[$i],6,2).'/'.substr($trn_dates[$i],0,4);
				  $je->amount = $trn_amnts[$i];
				  $je->note = $trn_names[$i];
				  $je->offset_account_id = null;
				  $this->view->JournalEntryList[] = $je;
				}
			}
		case 'square':
			$ret = self::_parseSquare($opt['file']);
			break;
		}

		return $ret;

	}

	/**
		Parse PayPal Transaction Data
	*/
	private static function _parsePayPal_v1($file)
	{
		require_once(__DIR__ . '/Reconcile_PayPal_v1.php');
		return Account_Reconcile_PayPal_v1::parse($file);
	}

	/**
		Parse WellsFargo CSV to Journal Entry Array
	*/
	private static function _parseWellsFargo($file)
	{
		$ret = array();
		$fh = fopen($file,'r');
		while ($csv = fgetcsv($fh,4096)) {

			if (count($csv) < 4) {
				continue;
			}

			// Journal entry
			$je = array();
			$je['date'] = $csv[0];
			$je['note'] = $csv[4];
			$je['ledger_entry_list'] = array();

			// Apply Filter Here?
			$je = self::_filterEntry($je);
			$je = self::_guessAccount($je);

			// Ledger Entry
			$le = array(
				'account_id' => null,
				'amount' => self::_filter_number($csv[1]),
			);

			if ($le['amount'] < 0) {
				$le['cr'] = abs($csv[1]);
			} else {
				$le['dr'] = abs($csv[1]);
			}

			$je['ledger_entry_list'][] = $le;

			$ret[] = $je;
		}

		return $ret;

	}

	/**
		Parse Square.com Transactions
		@todo Need to Make TWO entries
			  One to Square for the Full Amount #5
			  One to Payment Processors for Fee
	*/
	private static function _parseSquare($file)
	{
		$ret = array();
		$fh = fopen($file,'r');
		while ($csv = fgetcsv($fh,4096)) {
			$x = strtolower(trim($csv[0]));
			if ($x == 'date') continue;
			if (count($csv) < 4) continue;

			$je = new stdClass();
			$je->date = strftime('%Y-%m-%d %H:%M:%S',strtotime($csv[0]));
			$je->note = $csv[21] . '#' . $csv[22] . ' ' . $csv[26];
			$je->ledger = array();

			// Transaction Amount
			$le = array();
			$x = self::_filter_number($csv[19]); // Is it 13 or 19?
			if ($x < 0) {
				$le['cr'] = abs($x);
			} else {
				$le['dr'] = abs($x);
			}
			$je->ledger[] = $le;

			// Apply Filter Here?
			// $je = self::_filterEntry($je);
			// $je = self::_guessAccount($je);

			// The Fee Entry
			// $fee = floatval(preg_replace('/[^\d\.]+/',null,$csv[13]));
			$fee = self::_filter_number($csv[18]);
			$je->ledger[] = array(
				'note' => 'Fee for Transaction #' . $csv[20],
				'abs' => abs($fee),
				'cr' => $fee,
			);
			// = floatval(preg_replace('/[^\d\.]+/',null,$csv[13]));
			// $je->cr = floatval(preg_replace('/[^\d\.]+/',null,$csv[18]));
			// $je->amount = ($je->cr);

			$ret[] = $je;

		}
		return $ret;
	}

	/**
		Filter the Transactions?
	*/
	private static function _filterEntry($je)
	{
		// Wells Fargo Noise
		$je['note'] = str_replace('CHECK CRD PURCHASE ', null, $je['note']);
		$je['note'] = preg_replace('/^POS PURCHASE \- /', null, $je['note']);
		$je['note'] = preg_replace('/^PURCHASE AUTHORIZED ON /', null, $je['note']);

		return $je;
	}

	/**
		Guess the Opposition Account
	*/
	private static function _guessAccount($je)
	{
		return $je;
	}

	/**
		Sorts Journal Entries
	*/
	private static function _sortCallback($a,$b)
	{
		// Compare by Time (Lowest First)
		$x0 = strtotime($a['date']);
		$x1 = strtotime($b['date']);
		if ($x0 != $x1) {
			return ($x0 > $x1);
		}

		// Compare by Amount (Highest First)
		return ($a['amount'] < $b['amount']);
	}
}
