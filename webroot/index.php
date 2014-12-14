<?php
/**
    @file
    @brief Web Handler for Edoceo Imperium
*/

namespace Edoceo\Imperium;

use \Radix;

// Uncomment to get timing outputs
define('APP_INIT', microtime(true));

header('Cache-Control: no-cache, must-revalidate');

require_once(dirname(dirname(__FILE__)) . '/boot.php');

// User Specified Theme
if (!empty($_GET['_t'])) {
    $opt['theme'] = $_GET['_t'];
}
if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	$opt['theme'] = 'ajax';
}

Radix::init($opt);
Radix\Session::init(array('name' => 'imperium'));

// Zend_Controller_Front
// $front = Zend_Controller_Front::getInstance();
// $front->setControllerDirectory('../approot/controllers');
// $front->throwExceptions(true);
// $front->setParam('noErrorHandler', true);

// Add Routes
// $router = $front->getRouter();
// Controller/Action/ID Default
// $router->addRoute('c-a-id',new Zend_Controller_Router_Route('/:controller/:action/:id'));

// Email Actions
// $router->addRoute('email-folder-view',
//    new Zend_Controller_Router_Route_Regex('email/([\w\.\-]+@[\w\.\-]+)',array('controller'=>'Email','action'=>'viewFolder')));
// $router->addRoute('email-message-view',
//    new Zend_Controller_Router_Route_Regex('email/([\w\.\-]+@[\w\.\-]+)/(\d+)',array('controller'=>'Email','action'=>'viewMessage')));

// Login / Logout
// $router->addRoute('hash',new Zend_Controller_Router_Route('hash/:hash',array('controller'=>'Index','action'=>'hash')));
// $router->addRoute('login',new Zend_Controller_Router_Route('login',array('controller'=>'Index','action'=>'login')));
// $router->addRoute('logout',new Zend_Controller_Router_Route('logout',array('controller'=>'Index','action'=>'logout')));

// Checkout Link for Invoices
// $router->addRoute('checkout-invoice',new Zend_Controller_Router_Route('checkout/invoice/:hash',array('controller'=>'checkout','action'=>'invoice')));

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
	$_SESSION['uid'] = Radix\DB\SQL::fetch_one($sql,$arg);
}

// radix_acl::permit('null','/auth/*');
if (empty($_SESSION['uid'])) {
	unset($_SESSION['_acl']);
	ACL::permit('/auth/sign-in');
	ACL::permit('/auth/sign-in', 'POST');
	ACL::permit('/auth/sign-out');
	ACL::permit('/hook/*');
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
		Radix\Session::flash('fail', 'Identity Required');
		Radix::redirect('/auth/sign-in');
	}

	Radix\Session::flash('fail', 'Access Denied to ' . Radix::$path);
	Radix::redirect('/');

}

Radix::exec();
Radix::view();
Radix::send();

// Output Statistics
if (defined('APP_INIT')) {

    $res = getrusage();
    $mem = sprintf('%0.1f', memory_get_peak_usage(true) / 1024);
    $sec = sprintf('%0.4f', microtime(true) - APP_INIT);

    echo "\n<!--\n";

    echo "mem: {$mem}KiB\n"; 
    echo "sec: {$sec}s\n";
    echo "bio: {$res['ru_inblock']}/{$res['ru_oublock']}\n";
    // echo 'Page Faults:  ' . $res['ru_minflt'] . "\n";
    //echo 'V-Context Switches: ' . $res['ru_nvcsw'] . "\n";
    //echo 'I-Context Switches: ' . $res['ru_nivcsw']  . "\n";
    /*
    $u0 = sprintf('%d.%06d',$_res_0['ru_utime.tv_sec'],$_res_0['ru_utime.tv_usec']);
    $s0 = sprintf('%d.%06d',$_res_0['ru_stime.tv_sec'],$_res_0['ru_stime.tv_usec']);
    $u1 = sprintf('%d.%06d',$res_1['ru_utime.tv_sec'],$res_1['ru_utime.tv_usec']);
    $s1 = sprintf('%d.%06d',$res_1['ru_stime.tv_sec'],$res_1['ru_stime.tv_usec']);
    */

    //$utime = number_format($u1 - $u0,3);
    //$stime = number_format($s1 - $s0,3);
    //$rtime = number_format(((float)$new_usec + (float)$new_sec) - ((float)$old_usec + (float)$old_sec),3);

    //echo 'uname: ' . php_uname() . "\n";
    // $a = get_included_files();
    // sort($a);
    // echo implode("\n",$a);
    // foreach ($a as $b) {
    //   echo "$b\n";
    // }
    // */
    // print_r($res);
    echo "\n-->";
}
