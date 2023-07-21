<?php
/**
 * Parse the BECU CSV Format
 */

namespace Edoceo\Imperium\Account\Import;

class BECU extends \Edoceo\Imperium\Account\Import
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
			throw new \Exception('Invalid BECU Data File [AIB-023]');
		}

		$this->account_id = $account;

		$head_row = fgetcsv($this->csv, 4096);
		$head0 = implode(',', $head_row);
		$head1 = 'Account Number,Post Date,Check,Description,Debit,Credit,Status,Balance';

		if ($head0 !== $head1) {
			throw new \Exception('Invalid BECU Data File [AIB-030]');
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

			$note_tmp = [];
			$note_tmp[] = $rec['Status'];
			$note_tmp[] = $rec['Description'];
			if ( ! empty($rec['Check'])) {
				$note_tmp[] = sprintf('Check #%s', $rec['Check']);
			}

			$je = [];
			$je['date'] = $rec['Post Date'];
			$je['note'] = implode('; ', $note_tmp);
			$je['ledger_entry_list'] = [];

			$le = [];
			$le['account_id'] = $this->account_id;
			$le['cr'] = abs($this->filter_number($rec['Credit']));
			$le['dr'] = abs($this->filter_number($rec['Debit']));

			$je['ledger_entry_list'][] = $le;

			$ret[] = $je;
		}

		// var_dump($ret); exit;

		return $ret;
	}

}
