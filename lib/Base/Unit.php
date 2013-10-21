<?php
/**
  Represents a Base Unit of Measurement
*/

class Base_Unit
{
    static function getList()
    {
    	$sql = 'select id,name from base_unit order by name';
        return radix_db_sql::fetch_mix($sql);
    }
}