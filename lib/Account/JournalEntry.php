<?php
/**
	@copyright	2008 Edoceo, Inc
  @package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

class AccountJournalEntry extends ImperiumBase
{
	protected $_table = 'account_journal';

	// const OBJECT_TYPE = 101;

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
    if (strlen(trim($this->note))==0) {
      $this->note = new Zend_Db_Expr('null');
    }
    return parent::save();
  }
}
