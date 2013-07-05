<?php
/**
    CLI Libraries
*/


// Configurations

$cli_opt = getopt('c:d:s',array('config:','date:','diff:','send'));

if (empty($cli_opt['config']) && !empty($cli_opt['c'])) $cli_opt['config'] = $cli_opt['c'];
if (!empty($cli_opt['config'])) putenv('IMPERIUM_CONFIG=' . $cli_opt['config']);

$time = mktime(0,0,0);
if (!empty($cli_opt['d'])) $cli_opt['date'] = $cli_opt['d'];
if (!empty($cli_opt['date'])) $time = strtotime($cli_opt['date']);
$date = strftime('%Y-%m-%d',$time);

if (isset($cli_opt['s']) || isset($cli_opt['send'])) $cli_opt['send'] = true;

// Bootstrapper
require_once(dirname(dirname(dirname(__FILE__))) . '/boot.php');

$db = Zend_Registry::get('db');

$za = Zend_Auth::getInstance();
$za->authenticate(new App_Auth($_ENV['cron']['username'],$_ENV['cron']['password']));
$cu = $za->getIdentity();
if (empty($cu)) {
    die("Invalid Username or Password");
}

$uri = parse_url($_ENV['mail']['smtp']);
$_ENV['smtp_from'] = $uri['user'];

// Email Summary
$smtp = new Zend_Mail_Transport_Smtp($uri['host'],array(
    'auth' => 'login',
    'username' => $uri['user'],
    'password' => $uri['pass'],
    'ssl'  => 'tls',
    'port' => $uri['port'],
));

function _new_mail()
{
    $r = new Zend_Mail();
    $r->addHeader('X-MailGenerator','Edoceo Imperium');
    // print_r($_ENV); exit;
    $r->setFrom($_ENV['smtp_from'],$_ENV['company']['name']);
    // $mail->setFrom($cu->username);

    return $r;
}