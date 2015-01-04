<?php
/**
	AppAuth
	Imperium Authentication Adapter

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1081
	@version	$Id$
*/

class App_Auth implements Zend_Auth_Adapter_Interface
{
	function __construct($u,$p)
	{
		$this->username = $u;
		$this->password = $p;
	}

	function authenticate()
	{
		$db = Zend_Registry::get('db');

		//$sql = sprintf('SELECT * FROM auth_user WHERE username = $1 AND (password = $2 OR password = $3)');
		$sql = $db->select();
		$sql->from('auth_user');
		$sql->where('username = ?',$this->username);
		// Reads Plaintext
		$sql->where('password = ?',$this->password);
		// Or Encrypted Password
		$sql->orWhere('password = ?',ImperiumUser::makePassword($this->username,$this->password));
		// $sql = sprintf("select id from auth_user where username = '%s' and password = '%s'",$u,$p);
		$id = $db->fetchOne($sql);
		if ($id) {
			$x = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, new ImperiumUser($id));
			return $x;
		}
		//$e = sprintf('General Authentication Failure %s',ImperiumUser::makePassword($this->username,$this->password));
		$e = 'General Authentication Failure';
		$x = new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null,array($e));
		return $x;
	}
}
