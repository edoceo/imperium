<?php
/**
	@copyright	2008 Edoceo, Inc
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 2008.06.20
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class AccountJournalEntry extends ImperiumBase
{
	protected $_table = 'account_journal';

	/**
		Delete Ledger & Journal Entries
	*/
	function delete()
	{

		$arg = array(intval($this->_data['id']));

		SQL::query('DELETE FROM account_ledger where account_journal_id = ?', $arg);
		SQL::query('DELETE FROM account_journal where id = ?', $arg);

		return true;
	}
	/**
		AccountJournalEntry save
	*/
	function save()
	{
		if (empty($this->_data['date'])) {
			$this->_data['date'] = strftime('%Y-%m-%d');
		}

		if (strlen(trim($this->_data['note']))==0) {
			$this->_data['note'] = null;
		}

		return parent::save();
	}
}
