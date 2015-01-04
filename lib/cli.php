<?php
/**
    CLI Libraries
*/


// Configurations
$cli_opt = getopt('c:d:ms',array('config:','cron','date:','diff:','mail','send'));

if (empty($cli_opt['config']) && !empty($cli_opt['c'])) $cli_opt['config'] = $cli_opt['c'];
if (!empty($cli_opt['config'])) putenv('IMPERIUM_CONFIG=' . $cli_opt['config']);

$time = mktime(0,0,0);
if (!empty($cli_opt['d'])) $cli_opt['date'] = $cli_opt['d'];
if (!empty($cli_opt['date'])) $time = strtotime($cli_opt['date']);
$date = strftime('%Y-%m-%d',$time);

if (isset($cli_opt['m']) || isset($cli_opt['mail'])) $cli_opt['mail'] = true;
if (isset($cli_opt['s']) || isset($cli_opt['send'])) $cli_opt['send'] = true;

// Bootstrapper
require_once(dirname(dirname(__FILE__)) . '/boot.php');

// function _new_mail()
// {
//     $r = new Zend_Mail();
//     // print_r($_ENV); exit;
//     $r->setFrom($_ENV['smtp_from'],$_ENV['company']['name']);
//     // $mail->setFrom($cu->username);
// 
//     return $r;
// }