<?php
/**
 * Account Import Utility
 */

namespace Edoceo\Imperium\Account;

class Import
{

	/**
	 * Forces to a Number ([+|-\d\.]+)
	 */
	function filter_number($f)
	{
		// Replace non-digit like things with nothing
		$r = floatval(preg_replace('/[^\+\-\d\.]+/', '', $f));
		return $r;
	}

}
