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
// Merge Host
if ($x = getenv('IMPERIUM_CONFG')) {
    $x = APP_ROOT . '/etc/' . $x . '.ini';
    if ( (is_file($x)) && (is_readable($x)) ) {
        $_ENV = array_merge($_ENV,parse_ini_file($x,true));
    }
}

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
