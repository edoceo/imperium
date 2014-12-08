<?php
/**
  Represents a Base Unit of Measurement
*/

namespace Edoceo\Imperium;

use Radix;

class Base_Unit
{
    static function getList()
    {
    	$sql = 'select id,name from base_unit order by name';
        return Radix\DB\SQL::fetch_mix($sql);
    }
}