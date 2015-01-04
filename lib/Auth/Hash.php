<?php
/**
	Auth_Hash provides onetime access to Imperium Resources

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

namespace Edoceo\Imperium\Auth;

use Edoceo\Imperium\ImperiumBase;

class Hash extends ImperiumBase
{
	protected $_table = 'auth_hash';

	static function find($h)
	{
		$db = Zend_Registry::get('db');
		$h = pg_escape_string($h);
		$sql = "select * from auth_hash where hash='$h'";
		return $db->fetchRow($sql);
	}
	/**
		Auth_Hash makeAuth_Hash
		Create a new, persisted Auth_Hash
	*/
	static function make($x)
	{
		$ah = new self();
		$ah['link'] = $x->link();
		$data = serialize($ah).serialize($x);
		$ah['hash'] = substr(hash('sha1', $data),0,64);
		$ah->save();

		return $ah;

		// $t = new Zend_Db_Table(array('name'=>'auth_hash'));
		// $r = array();
		// $r['link'] = $x->link();
		// $r['hash'] = substr(hash('sha1',serialize($r).serialize($x)),0,64);

	}
}
