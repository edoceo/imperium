<?php
/**
	An AJAX Handler
*/

$q = strtolower(trim($_GET['term']));
if (strlen($q) == 1) {
	$q = '^' . $q;
}

switch ($_GET['a']) {
case 'account':

	// $s = $this->_d->select();
	// $s->from('account',array('id','full_name as label','full_name as result'));
	// $s->where('name ~* ?',$q);
	// $s->orWhere('full_name ~* ?','^'.$q);
	// // $s->orWhere('name ~* ?','^'.$q);
	// $s->order(array('full_name'));
	// $r = $this->_d->fetchAll($s);
	
	$sql = 'SELECT DISTINCT id, full_name AS label, full_name AS result';
	$sql.= ' FROM account';
	$sql.= ' WHERE name ~* ? OR full_name ~* ?';
	$sql.= ' ORDER BY full_name';
	$res = radix_db_sql::fetch_all($sql, array($q, "^$q"));
	die(json_encode($res));
	break;

case 'contact':

	$s = $this->_d->select();
	$s->from('contact',array('id','name as label','name as result'));
	$s->where('contact ~* ?',$q);
	$s->orWhere('company ~* ?','^'.$q);
	$s->orWhere('name ~* ?','^'.$q);
	$s->order(array('contact'));
	$r = radix_db_sql::fetch_all($s);

	echo json_encode($r);
}

exit;
