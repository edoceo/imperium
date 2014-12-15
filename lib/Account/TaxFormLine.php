<?php
/**
	Account Tax Form Line Model

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;;

class AccountTaxFormLine extends ImperiumBase
{
	protected $_table = 'account_tax_line';

	//function listAccounts($account_tax_line_id) {
	//
	//	$list = array();
	//
	//	$sql = "select a.id,a.full_name ";
	//	$sql.= " from account a ";
	//	$sql.= " where a.account_tax_line_id = '" . pg_escape_string($account_tax_line_id) . "'";
	//	$sql.= " order by a.full_code";
	//
	//	$rs = SQL::fetchAll($sql);
	//
	//	foreach ($rs as $x) {
	//		$list[$x[0]['id']] = $x[0]['full_name'];
	//	}
	//	return $list;
	//}

	static function listTaxForms() {
		$list = array();
		$rs = $this->query("select distinct form from account_tax_line");
		foreach ($rs as $x) {
			$list[$x[0]['form']] = $x[0]['form'];
		}
		return $list;
	}

	static function listTaxLines()
	{
		$sql = "SELECT account_tax_line.id, account_tax_form.name || ': ' || account_tax_line.line || ' - ' || account_tax_line.name AS name ";
		$sql.= ' FROM account_tax_line';
			$sql.= ' JOIN account_tax_form ON account_tax_line.account_tax_form_id = account_tax_form.id';
		$sql.= ' ORDER BY account_tax_form.name,account_tax_line.sort';
		$res = SQL::fetch_all($sql);
		return $res;

		// Specific Form?
		/*
		$list = array();
		$rs = $this->query("select id,name from account_tax_line where form = '" . pg_escape_string($form) ."' order by sort");
		foreach ($rs as $x) {
			$list[$x[0]['id']] = $x[0]['name'];
		}
		return $list;
		*/
	}


}