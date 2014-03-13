<?php
/**
	Account Tax Form Model

	@copyright	2008 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/

class AccountTaxForm extends ImperiumBase
{
	protected $_table = 'account_tax_form';

	function getTaxLines()
	{
		$sql = 'SELECT * FROM account_tax_line WHERE account_tax_form_id = ? ORDER BY line';
		$arg = array($this->_data['id']);
		$res = radix_db_sql::fetchAll($sql, $arg);
		return $res;
	}
}