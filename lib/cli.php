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
require_once(dirname(dirname(__FILE__)) . '/boot.php');

// $db = Zend_Registry::get('db');

// $za = Zend_Auth::getInstance();
// $za->authenticate(new App_Auth($_ENV['cron']['username'],$_ENV['cron']['password']));
// $cu = $za->getIdentity();
// if (empty($cu)) {
//     die("Invalid Username or Password");
// }

function send_mail($rcpt, $mail)
{
	$uri = parse_url($_ENV['mail']['smtp']);

	require_once('Radix/mail/smtp.php');
	$smtp = new radix_mail_smtp(sprintf('%s://%s:%d', $uri['scheme'], $uri['host'], $uri['port']));
	$smtp->ehlo($_ENV['application']['host']);
	$smtp->auth($uri['user'], $uri['pass']);
	$smtp->mailFrom($uri['user']);
	$smtp->rcptTo($rcpt);
	$smtp->data($mail);
	$res = $smtp->quit();
	if ($res[0]['code'] != 221) {
		print_r($res);
	}
}

// function _new_mail()
// {
//     $r = new Zend_Mail();
//     // print_r($_ENV); exit;
//     $r->setFrom($_ENV['smtp_from'],$_ENV['company']['name']);
//     // $mail->setFrom($cu->username);
// 
//     return $r;
// }