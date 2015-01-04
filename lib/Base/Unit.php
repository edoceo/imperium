<?php
/**
  Represents a Base Unit of Measurement
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class Base_Unit
{
	static function getList()
	{
		$sql = 'select id,name from base_unit order by name';
		return SQL::fetch_mix($sql);
	}
}