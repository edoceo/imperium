<?php
/**
	WorkorderController init
	Sets the ACL for this Controller
*/

//$acl = Zend_Registry::get('acl');
//if ($acl->has('workorder') == false) {
//	$acl->add( new Zend_Acl_Resource('workorder') );
//}
//$acl->allow('user','workorder');

// parent::init();

$sql = 'SELECT name AS id,name FROM base_enum WHERE link = ? ORDER BY sort';
$this->StatusList     = radix_db_sql::fetch_mix($sql, array('invoice-status'));
$this->ItemStatusList = radix_db_sql::fetch_mix($sql, array('invoice-item-status'));


