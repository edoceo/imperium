<?php
/**

*/

switch (strtolower($_POST['a'])) {
case 'sign in':

	if (!acl::may('/auth/sign-in', 'POST')) {
		radix_session::flash('fail', 'Access Denied');
		radix::redirect('/auth/sign-in');
	}

	$sql = 'SELECT * FROM auth_user WHERE username = ? ';
	$sql.= ' AND (password = ? OR password = ? )';
	$arg = array(
		strtolower($_POST['username']),
		$_POST['password'],
		sha1($_POST['username'] . $_POST['username']),
	);
	$res = radix_db_sql::fetchRow($sql, $arg);
	if (empty($res)) {
		// @todo Random Sleep
		radix_session::flash('fail', 'Invalid username or password');
		radix::redirect();
	}

	// radix::dump($res);
	$_SESSION['uid'] = $res['id'];

	acl::permit('/index');
	acl::permit('/dashboard');
	acl::permit('/block*');
	// acl::permit('/base/*');
	acl::permit('/account*');
	acl::permit('/contact*');
	acl::permit('/invoice*');
	acl::permit('/workorder*');
	acl::permit('/settings*');

	radix_session::flash('info', 'Sign In Successful');
	radix::redirect('/');

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
