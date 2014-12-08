<?php
/**

*/

namespace Edoceo\Imperium;

use Radix;
use Radix\DB;

switch (strtolower($_POST['a'])) {
case 'sign in':

	if (!acl::may('/auth/sign-in', 'POST')) {
		Session::flash('fail', 'Access Denied');
		Radix::redirect('/auth/sign-in');
	}

	$sql = 'SELECT * FROM auth_user WHERE username = ? ';
	$sql.= ' AND (password = ? OR password = ? )';
	$arg = array(
		strtolower($_POST['username']),
		$_POST['password'],
		sha1($_POST['username'] . $_POST['username']),
	);
	$res = Radix\DB\SQL::fetch_row($sql, $arg);
	if (empty($res)) {
		// @todo Random Sleep
		Radix\Session::flash('fail', 'Invalid username or password');
		Radix::redirect();
	}

	// radix::dump($res);
	$_SESSION['uid'] = $res['id'];

	acl::permit('/index');
	acl::permit('/dashboard');
	acl::permit('/search');
	acl::permit('/block*');
	acl::permit('/email*');
	acl::permit('/file*');
	acl::permit('/note*');
	acl::permit('/account*');
	acl::permit('/contact*');
	acl::permit('/invoice*');
	acl::permit('/workorder*');
	acl::permit('/settings*');

	Radix\Session::flash('info', 'Sign In Successful');

	// Redirect
	$ret = '/';
	if (!empty($_SESSION['return-path'])) {
		$ret = $_SESSION['return-path'];
		unset($_SESSION['return-path']);
	}
	radix::redirect($ret);

	break;
}

// $db = Zend_Registry::get('db');
// $ss = Zend_Registry::get('session');

// $this->view->title = 'Login';
// 
// $req = $this->getRequest();
// if ($req->isPost()) {
// 
// 	$auth = Zend_Auth::getInstance();
// 	$res = $auth->authenticate( new App_Auth($req->getPost('username'),$req->getPost('password')) );
// 	if ($res->isValid()) {
// 		$this->redirect('/');
// 	} else {
// 		$ss->fail = $res->getMessages();
// 		$this->redirect('/login');
// 	}
// }
