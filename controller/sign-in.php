<?php
/**

*/

radix::$theme_name = 'sign-in';

$_ENV['title'] = 'Sign In';

if (count($_POST)) {

    radix::dump($_POST);

    $sql = 'SELECT * FROM auth_user'; //  WHERE username = ? AND password = ?';
    $arg = array($_POST['username'],$_POST['password']);
    $res = radix_db_sql::fetch_row($sql,$arg);
    radix::dump(radix_db_sql::lastError());
    radix::dump($res);

    // $res = $auth->authenticate( new App_Auth($req->getPost('username'),$req->getPost('password')) );
    // if ($res->isValid()) {
    //     $this->redirect('/');
    // } else {
    //     $ss->fail = $res->getMessages();
    //     $this->redirect('/login');
    // }
}
