<?php
/**
	@copyright	2008 Edoceo, Inc
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

class AccountJournalEntry extends ImperiumBase
{
	protected $_table = 'account_journal';

	public $note;
	public $date;
	public $kind = 'N';

	/**
	*/
	function delete()
	{

		$id = intval($this->id);

		$db = Zend_Registry::get('db');
		$db->query("delete from account_ledger where account_journal_id = $id");
		$db->query("delete from account_journal where id = $id");

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
