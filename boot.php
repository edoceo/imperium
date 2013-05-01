<?php
/**
    @file
    @brief Bootstraps the Imperium Application

    @deprecated ReturnTo => ReturnPath
    @deprecated ReturnGood => ReturnPath ? ret=1
    @deprecated ReturnFail => ReturnPath ? ret=0
*/

error_reporting( (E_ALL | E_STRICT) ^ E_NOTICE );

$path = array();
// $path[] = dirname(__FILE__) . '/lib';
$path[] = dirname(__FILE__) . '/approot/lib';
// Use this for a Zend Trunk or Specific Version
$path[] = '/opt/Zend-1.11';
// Get PHPs stuffs
$path[] = get_include_path();

set_include_path(implode(PATH_SEPARATOR,$path));

define('APP_ROOT',dirname(__FILE__));
define('APP_NAME','Edoceo Imperium');

// Use Zend Loader (>=1.8)
require_once('Zend/Loader/Autoloader.php');
$al = Zend_Loader_Autoloader::getInstance();
$al->setFallbackAutoloader(true);

// Load Application Config
$_ENV = parse_ini_file(dirname(__FILE__).'/approot/etc/imperium.ini',true);
$_ENV = array_change_key_case($_ENV);
// Zend_Debug::dump($_ENV);

// Merge Local
$x = dirname(__FILE__).'/approot/etc/imperium-local.ini';
if ( (is_file($x)) && (is_readable($x)) ) {
    $x = parse_ini_file($x,true);
    $x = array_change_key_case($x);
    $_ENV = array_merge_recursive($_ENV,$x);
    // Zend_Debug::dump($_ENV);
}
// Merge Host
if ($x = getenv('IMPERIUM_CONFIG')) {
    $x = APP_ROOT . '/approot/etc/' . $x . '.ini';
    if ( (is_file($x)) && (is_readable($x)) ) {
        $x = parse_ini_file($x,true);
        $x = array_change_key_case($x);
        $_ENV = array_merge_recursive($_ENV,$x);
        // Zend_Debug::dump($_ENV);
    }
}
// Reduce to Singular Values
foreach ($_ENV as $k0=>$opt) {
    foreach ($opt as $k1=>$x) {
        if (is_array($_ENV[$k0][$k1])) {
            while (count($_ENV[$k0][$k1]) > 1) {
                array_shift($_ENV[$k0][$k1]);
            }
            $_ENV[$k0][$k1] = $_ENV[$k0][$k1][0];
        }
    }
}

ini_set('date.timezone',$_ENV['application']['zone']);
date_default_timezone_set($_ENV['application']['zone']);

// Zend Locale
//$locale  = new Zend_Locale('en');
Zend_Locale::setDefault('en_US');
Zend_Registry::set('Zend_Locale', new Zend_Locale('en_US'));

// Date Options
Zend_Date::setOptions(array('extend_month' => false,'format_type'=>'iso'));

// Zend Database
$x = $_ENV['database'];
unset($x['adapter']);
$x = Zend_Db::factory($_ENV['database']['adapter'],$x);
$x->setFetchMode(Zend_Db::FETCH_OBJ);
// set client_encoding='utf-8';
Zend_Registry::set('db',$x);
Zend_Db_Table_Abstract::setDefaultAdapter($x);

/**
    Internal Hax0r Functions
*/
function html($x)
{
    return htmlspecialchars($x,ENT_QUOTES,'UTF-8',false);
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

    $src = $_ENV['star'][$star];
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
