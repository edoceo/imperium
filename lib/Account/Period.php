<?php
/**
	Account Period Model
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class AccountPeriod extends ImperiumBase
{
	/**
		Find by Date
	*/
	static function findByDate($d)
	{
		$ret = null;

		$sql = 'SELECT * FROM account_period WHERE date_alpha <= ? AND date_omega >= ?';
		$arg = array($d, $d);
		$res = SQL::fetch_row($sql, $arg);

		if (!empty($res)) {
			$ret = new self($res);
		}

		return $ret;
	}

	/**
		Is Closed or Not?
		@return boolean
	*/
	function isClosed()
	{
		return ($this->_data['status_id'] == 200);
	}

}
