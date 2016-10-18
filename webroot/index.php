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

// User Specified Theme
if (!empty($_GET['_t'])) {
    $opt['theme'] = $_GET['_t'];
}
if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	$opt['theme'] = 'ajax';
}

Radix::init($opt);
Session::init(array('name' => 'imperium'));

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
