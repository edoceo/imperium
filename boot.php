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
require_once('Radix/ACL.php');
require_once('Radix/db/sql.php');
require_once('Radix/SQL_Record.php');

// Load Application Config
$_ENV = parse_ini_file(APP_ROOT . '/etc/boot.ini',true);
// Merge Local
// $x = dirname(__FILE__).'/imperium-local.ini';
// if ( (is_file($x)) && (is_readable($x)) ) {
//     $x = parse_ini_file($x,true);
//     $_ENV = array_replace_recursive($_ENV,$x);
// }
// Merge Host
if ($x = getenv('IMPERIUM_CONFIG')) {
    $x = APP_ROOT . '/etc/' . $x . '.ini';
    if ( (is_file($x)) && (is_readable($x)) ) {
        $x = parse_ini_file($x,true);
        $_ENV = array_replace_recursive($_ENV,$x);
    }
}
$_ENV = array_change_key_case($_ENV);

switch ($_ENV['database']['kind']) {
case 'pgsql':
    radix_db_sql::init('pgsql:' . $_ENV['database']['path'],$_ENV['database']['user'],$_ENV['database']['pass']);
    break;
case 'sqlite':
    radix_db_sql::init('sqlite:' . APP_ROOT . '/var/' . $_ENV['database']['file']);
    break;
default:
    // Fail
}
unset($_ENV['database']); // exclude from ENV

function html($x) { return htmlspecialchars($x,ENT_QUOTES,'UTF-8',false); }
function stub($x)
{
    $x = preg_replace('/[^\w\-\.]+/','.',$x);
    $x = preg_replace('/\.+/','.',$x);
    $x = strtolower(trim($x));
    return $x;
}
function img($img,$alt=null)
{
    $src = !empty($_SERVER['HTTPS']) ? 'https:' : 'http:';
    $src.= '//gcdn.org/' . ltrim($img,'/');
    if (empty($alt)) {
        $alt = strtok(basename($img),'.');
    }
    return '<img alt="' . htmlspecialchars($alt,ENT_QUOTES) . '" src="' . $src . '" />';
}

function star($star)
{
    if (empty($star)) return null;

    $src = $_ENV['Star'][$star];
    $alt = basename($img,'.png');

    $ret = '<img alt="' . $alt . '" class="star" data="' . $star . '" id="star" src="' . $src . '">';
    return $ret;
}

/**
    Takes Input Text and Return Tax as Floating Point 9.5% => 0.095
*/
function tax_rate_fix($x)
{
    // Estimated Tax Rate Adjuster
    if (preg_match('/([\d\.]+).*%$/',$x,$m)) {
        // The input number was like: 9.5% or 9.5 % - numbers that end with "%" symbol
        $x = floatval($m[1]) / 100;
    }
    $x = floatval($x);
    if ($x > 1) {
        // Not expressed as percentage (it's 9.5 but should be 0.095), /100 to get actual
        $x = floatval($x / 100);
    }
    return $x;
}

function tax_rate_format($x)
{
    $x = floatval($x);
    if ($x < 1) {
        $x = sprintf('%0.4f',$x * 100);
        $x = preg_replace('/0+$/',null,$x);
        $x.= '%';
    }
    return $x;
}
