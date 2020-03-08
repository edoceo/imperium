<?php
/**
    @file
    @brief Web Handler for Edoceo Imperium
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

header('Cache-Control: no-cache, must-revalidate');

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// Mangle SERVER data
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// Next two trick PHP/Radix into thinking it's under SSL
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
	if ('https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_PORT'] = 443;
	}
}

// User Specified Theme
if (!empty($_GET['_t'])) {
    $opt['theme'] = $_GET['_t'];
}
if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	$opt['theme'] = 'ajax';
}

Radix::init($opt);
Session::init(array('name' => 'imperium'));

// Zend_Acl Create Access Control List
// $acl = new Zend_Acl();
// $acl->add( new Zend_Acl_Resource('index') );
// $acl->add( new Zend_Acl_Resource('error') );
// @todo These should be added on-demand like for other modules
//$acl->add( new Zend_Acl_Resource('contact') );
//$acl->add( new Zend_Acl_Resource('contact.controller') );
//$acl->add( new Zend_Acl_Resource('invoice') );
//$acl->add( new Zend_Acl_Resource('workorder') );
//$acl->add( new Zend_Acl_Resource('workorder.item') );

// $acl->addRole( new Zend_Acl_Role('null') );
// $acl->addRole( new Zend_Acl_Role('root') );
// $acl->addRole( new Zend_Acl_Role('user'), 'null' ); // inherit from null
//$acl->addRole( new Zend_Acl_Role($username), 'root' ); // inherit from root

// Zend_Auth
// $auth = Zend_Auth::getInstance();

// Force Global Login
$x = $_ENV['application']['auto_username'];
if (!empty($x)) {
    // $auth->authenticate( new App_Auth($x,$_ENV['application']['auto_password'] ) );
	$sql = 'SELECT id FROM auth_user WHERE username = ? AND password = ?';
	$arg = array($_ENV['application']['auto_username'],$_ENV['application']['auto_username']);
	$_SESSION['uid'] = SQL::fetch_one($sql,$arg);
}

// radix_acl::permit('null','/auth/*');
if (empty($_SESSION['uid'])) {
	unset($_SESSION['_acl']);
	ACL::permit('/auth/sign-in');
	ACL::permit('/auth/sign-in', 'POST');
	ACL::permit('/auth/sign-out');
	ACL::permit('/hook/*');
}

$m = strtok(Radix::$path, '/');
switch ($m) {
case 'api':

	// Sanatize Path (shitty, make a better one)
	$file = Radix::$path;
	$file = str_replace('../', '/', $file);
	$file = str_replace('./', '/', $file);
	$file = str_replace('//', '/', $file);
	$file = sprintf('%s/controller%s.php', APP_ROOT, Radix::$path);
	if (is_file($file)) {
		require_once($file);
	}

	exit(0);

	break;
}

if (!acl::may(Radix::$path)) {

	if (empty($_SESSION['uid'])) {
		if (empty($_SESSION['return-path'])) {
			if (!empty($_SERVER['REQUEST_URI'])) {
				$real_base = Radix::base();
				$want = $_SERVER['REQUEST_URI'];
				$want_base = substr($want, 0, strlen($real_base));
				if ($real_base == $want_base) {
					$_SESSION['return-path'] = substr($want, strlen($real_base));
				}
			}
		}
		Session::flash('fail', 'Identity Required');
		Radix::redirect('/auth/sign-in');
	}

	Session::flash('fail', 'Access Denied to ' . Radix::$path);
	Radix::redirect('/');

}

Radix::exec();
Radix::view();
Radix::send();
