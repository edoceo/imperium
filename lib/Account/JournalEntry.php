<?php
/**
	@copyright	2008 Edoceo, Inc
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 2008.06.20
*/

namespace Edoceo\Imperium;

class AccountJournalEntry extends ImperiumBase
{
	protected $_table = 'account_journal';

	function __construct($x)
	{
		parent::__construct($x);
		if (empty($this->_data['date'])) {
			$this->_data['date'] = strftime('%Y-%m-%d');
		}
	}

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
		if (strlen(trim($this->_data['note']))==0) {
			$this->_data['note'] = null;
		}

		return parent::save();
	}
}
