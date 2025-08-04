<?php
/**
 * Parse the Chase.com CC CSV Format
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium\Account\Import;

class Chase extends \Edoceo\Imperium\Account\Import
{
	private $csv;

	private $file;

	private $head;

	/**
	 *
	 */
	function __construct(string $file, $account)
	{
		$this->csv = fopen($file, 'r');
		if (empty($this->csv)) {
			throw new \Exception('Invalid Data File [AIC-023]');
		}

		$this->account_id = $account;

		$head_row = fgetcsv($this->csv, 4096);
		$head0 = implode(',', $head_row);
		$head1 = 'Transaction Date,Post Date,Description,Category,Type,Amount,Memo';

		if ($head0 !== $head1) {
			throw new \Exception('Invalid Data File [AIC-033]');
		}

		$this->head = $head_row;

	}

	/**
	 *
	 */
	function parse()
	{
		while ($rec = fgetcsv($this->csv, 4096)) {

			$rec = array_combine($this->head, $rec);
			// var_dump($rec); exit;
			// ["Transaction Date"]=> string(10) "12/28/2023"
			// ["Post Date"]=> string(10) "12/29/2023"
			// ["Description"]=> string(10) "SIGNALWIRE"
			// ["Category"]=> string(8) "Shopping"
			// ["Type"]=> string(4) "Sale"
			// ["Amount"]=> string(6) "-10.00"
			// ["Memo"]=> string(0) ""

			$note_tmp = [];
			$note_tmp[] = $rec['Transaction Date'];
			$note_tmp[] = $rec['Type'];
			$note_tmp[] = $rec['Description'];
			$note_tmp[] = $rec['Memo'];
			$note_tmp[] = sprintf('[%s]', $rec['Category']);
			$note_tmp = array_filter($note_tmp);

			$je = [];
			$je['date'] = $rec['Post Date'];
			$je['note'] = implode('; ', $note_tmp);
			$je['ledger_entry_list'] = [];

			$le = [];
			$le['account_id'] = $this->account_id;

			// CR Increases the Balance of Liability
			$v = $this->filter_number($rec['Amount']);
			if ($v > 0) {
				$le['dr'] = abs($v);
			} elseif ($v < 0) {
				$le['cr'] = abs($v);
			}
			// switch ($rec['Type']) {
			// 	case 'Sale':
			// 		$le['cr'] = abs();
			// 		break;
			// 	case 'Payment':
			// 		$le['dr'] = abs($this->filter_number($rec['Amount']));
			// 		break;
			// 	case 'Adjustment':
			// 	case 'Return':
			// }

			$je['ledger_entry_list'][] = $le;

			$ret[] = $je;
		}

		return $ret;

	}

}
