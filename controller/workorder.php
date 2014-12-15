<?php
/**
	WorkorderController init
	Sets the ACL for this Controller
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

//$acl = Zend_Registry::get('acl');
//if ($acl->has('workorder') == false) {
//	$acl->add( new Zend_Acl_Resource('workorder') );
//}
//$acl->allow('user','workorder');

// parent::init();

$sql = 'SELECT name AS id,name FROM base_enum WHERE link = ? ORDER BY sort';
$this->KindList       = SQL::fetch_mix($sql, array('workorder-kind'));
$this->StatusList     = SQL::fetch_mix($sql, array('workorder-status'));
$this->ItemStatusList = SQL::fetch_mix($sql, array('workorder-item-status'));
