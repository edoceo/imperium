<?php
/**
    Edoceo Imperium Front-Controller
*/

// Uncomment to get Outputs
$s0 = microtime(true);

header('Cache-Control: no-cache, must-revalidate');
header('Cache-Control: no-cache, must-revalidate');

require_once(dirname(dirname(__FILE__)) . '/boot.php');
require_once('Radix/Session.php');
radix_session::init();

// Zend Session
// Zend_Session::start(array(
//   // 'cookie_path' => '/imperium',
//   'name'=> 'imperium',
//   'use_cookies'=>true,
//   'use_only_cookies'=>true
// ));
// $x = new Zend_Session_Namespace('default',true);
// Zend_Registry::set('session',$x);

// Zend_Controller_Front
// $front = Zend_Controller_Front::getInstance();
// $front->setControllerDirectory('../approot/controllers');
//$front->throwExceptions(true);
//$front->setParam('noErrorHandler', true);

// Add Routes
// $router = $front->getRouter();
// // Controller/Action/ID Default
// $router->addRoute('c-a-id',new Zend_Controller_Router_Route('/:controller/:action/:id'));

// Email Actions
//$router->addRoute('email-folder-view',
//    new Zend_Controller_Router_Route_Regex('email/([\w\.\-]+@[\w\.\-]+)',array('controller'=>'Email','action'=>'viewFolder')));
//$router->addRoute('email-message-view',
//    new Zend_Controller_Router_Route_Regex('email/([\w\.\-]+@[\w\.\-]+)/(\d+)',array('controller'=>'Email','action'=>'viewMessage')));

// Login / Logout
// Undo Some Here
// $router->addRoute('hash',new Zend_Controller_Router_Route('hash/:hash',array('controller'=>'Index','action'=>'hash')));
// $router->addRoute('login',new Zend_Controller_Router_Route('login',array('controller'=>'Index','action'=>'login')));
// $router->addRoute('logout',new Zend_Controller_Router_Route('logout',array('controller'=>'Index','action'=>'logout')));
// // Checkout Link for Invoices
// $router->addRoute('checkout-invoice',new Zend_Controller_Router_Route('checkout/invoice/:hash',array('controller'=>'checkout','action'=>'invoice')));
// //$router->addRoute('accounting-wizard',new Zend_Controller_Router_Route('/accounts/wizard',array('controller'=>'account_wizard')));

// Zend_Acl Create Access Control List
//$acl = new Zend_Acl();
//$acl->add( new Zend_Acl_Resource('index') );
//$acl->add( new Zend_Acl_Resource('error') );
// @todo These should be added on-demand like for other modules
//$acl->add( new Zend_Acl_Resource('contact') );
//$acl->add( new Zend_Acl_Resource('contact.controller') );
//$acl->add( new Zend_Acl_Resource('invoice') );
//$acl->add( new Zend_Acl_Resource('workorder') );
//$acl->add( new Zend_Acl_Resource('workorder.item') );
//$acl->addRole( new Zend_Acl_Role('null') );
//$acl->addRole( new Zend_Acl_Role('root') );
//$acl->addRole( new Zend_Acl_Role('user'), 'null' ); // inherit from null
//$acl->addRole( new Zend_Acl_Role($username), 'root' ); // inherit from root

// Zend_Auth
// $auth = Zend_Auth::getInstance();
// Force Global Login
// $x = App_Config::get('Application.auto_username');
// if (!empty($x)) {
//     $auth->authenticate( new App_Auth($x,App_Config::get('Application.auto_password') ) );
// }
// 
// // If Someone is logged in then they inherit from the 'User' role
// if ($auth->hasIdentity()) {
//     $cu = $auth->getIdentity();
//     if (!$acl->hasRole($cu->username)) {
//         $acl->addRole( new Zend_Acl_Role($cu->username), 'user' );
//     }
// }
// // Root Gets all Access
// $acl->allow('root');
// Zend_Registry::set('acl',$acl);

if (!empty($_GET['_t'])) {
    $opts['theme'] = $_GET['_t'];
}

radix::init($opts);
radix::exec();
radix::view();
radix::send();

// Output Statistics
if (!empty($s0)) {

    $res = getrusage();
    $mem = sprintf('%0.1f',memory_get_peak_usage(true) / 1024);
    $sec = sprintf('%0.4f',microtime(true) - $s0);

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
    $a = get_included_files();
    sort($a);
    echo implode("\n",$a);
    // foreach ($a as $b) {
    //   echo "$b\n";
    // }
    // */
    print_r($res);
    echo "\n-->";
}

exit(0);
