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
$path[] = dirname(__FILE__) . '/lib';
$path[] = '/opt/edoceo/lib/radix';
$path[] = get_include_path();
set_include_path(implode(PATH_SEPARATOR,$path));

define('APP_ROOT',dirname(__FILE__));
define('APP_NAME','Edoceo Imperium');

require_once('Radix.php');
require_once('Radix/ACL.php');
require_once('Radix/Filter.php');
require_once('Radix/Format.php');
require_once('Radix/Session.php');
require_once('Radix/html/form.php');

require_once('App.php');
require_once('App_Mail.php');
require_once('ACL.php');
require_once('ImperiumBase.php');
require_once('ImperiumView.php');
require_once('Base/File.php');
require_once('Base/Note.php');
// require_once('Base/Diff.php');
require_once('Base/Unit.php');
require_once('Account.php');
require_once('Account/JournalEntry.php');
require_once('Account/LedgerEntry.php');
require_once('Contact.php');
require_once('Contact/Address.php');
require_once('Contact/Channel.php');
require_once('Invoice.php');
require_once('InvoiceItem.php');
require_once('WorkOrder.php');
require_once('WorkOrderItem.php');

require_once(APP_ROOT . '/vendor/autoload.php');

// Load Application Config
App::load_config();

// Zend Locale
// $locale  = new Zend_Locale('en');
// Zend_Locale::setDefault('en_US');
// Zend_Registry::set('Zend_Locale', new Zend_Locale('en_US'));

// Date Options
// Zend_Date::setOptions(array('extend_month' => false,'format_type'=>'iso'));

// Zend Database
//$x = $_ENV['database'];
//unset($x['adapter']);
//$x = Zend_Db::factory($_ENV['database']['adapter'],$x);
//$x->setFetchMode(Zend_Db::FETCH_OBJ);
//// set client_encoding='utf-8';
//Zend_Registry::set('db',$x);
//Zend_Db_Table_Abstract::setDefaultAdapter($x);
require_once('Radix/db/sql.php');
radix_db_sql::init("pgsql:host={$_ENV['database']['hostname']};dbname={$_ENV['database']['database']}",$_ENV['database']['username'],$_ENV['database']['password']);
// App::$db = new radix_db_sql();

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