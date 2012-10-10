<?php
/**
	@file
	@brief Bootstrapper for Imperium
*/

error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

$path = array();
$path[] = dirname(__FILE__).'/lib';
$path[] = '/opt/edoceo/lib/radix';
$path[] = get_include_path();
set_include_path(implode(PATH_SEPARATOR,$path));

define('APP_ROOT',dirname(__FILE__));

require_once('Radix.php');

// require_once('Radix/Filter.php');

// require_once('Radix/SHM.php');
require_once('Radix/SQL.php');
require_once('Radix/SQL_Record.php');

// Load Configuration into ENV
$_ENV = parse_ini_file(APP_ROOT . '/etc/boot.ini',true);
switch ($_ENV['Database']['kind']) {
case 'pgsql':
    Radix_SQL::init('pgsql:' . $_ENV['Database']['path'],$_ENV['Database']['user'],$_ENV['Database']['pass']);
    break;
case 'sqlite':
    Radix_SQL::init('sqlite:' . APP_ROOT . '/var/' . $_ENV['Database']['file']);
    break;
default:
    // Fail
}
unset($_ENV['Database']); // exclude from ENV

function html($x) { return htmlspecialchars($x,ENT_QUOTES,'UTF-8',false); }
function stub($x)
{
    $x = preg_replace('/[^\w\-\.]+/','.',$x);
    $x = preg_replace('/\.+/','.',$x);
    $x = strtolower(trim($x));
    return $x;
}
