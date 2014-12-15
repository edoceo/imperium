<?php
/**
	@file
	@brief Contact Ajax Handler

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

switch (strtolower($_GET['field'])) {
case 'google': // Load the Google Data

	$GA = new Google_Account($_ENV['google']['username'],$_ENV['google']['password']);
	$gdf = $GA->getContact($this->_s->Contact->google_contact_id);
	echo '<pre> ' . html(print_r($gdf,true)) . '</pre>';
	//$xml = simplexml_load_string( $gdf->getXML() );
	// echo '<pre> ' . html(str_replace('><',">\n<",$xml->asXML())) . '</pre>';
	// exit(0);
	exit(0); // return(true);
}

// Query Handling
$q = trim($_GET['term']);
if (strlen($q) == 1) {
	$q = '^' . $q;
}

// $s = $this->_d->select();
// $s->from('contact',array('id','name as result','name as label','email','phone','contact','company'));
$arg = array();
$sql = 'SELECT id, name AS result, name AS label, email, phone, contact, company FROM contact ';
switch (strtolower($_GET['field'])) {
case 'company':
	// $s->where('company ~* ?',$q);
	$sql.= 'WHERE company ~* ?';
	$arg[] = $q;
	break;
case 'email':
	Radix::bail(500);
	$s = $this->_d->select();
	$s->from('contact',array('email as result','name as label'));
	$s->where('contact ~* ?',$q);
	$s->orWhere('company ~* ?',$q);
	$s->orWhere('name ~* ?',$q);
	$s->distinct();
	break;
case 'kind':
	Radix::bail(500);
	$s = $this->_d->select();
	$s->from('contact',array('kind as result','kind as label'));
	$s->distinct();
	break;
case 'status':
	Radix::bail(500);
	$s = $this->_d->select();
	$s->distinct();
	$s->from('contact',array('status as result','status as label'));
	break;
default:
	$sql.= 'WHERE contact ~* ? OR company ~* ? OR name ~* ? OR email ~* ?';
	$arg[] = $q;
	$arg[] = $q;
	$arg[] = $q;
	$arg[] = $q;
	break;
}

$res = SQL::fetch_all($sql, $arg);

header('Content-Type: application/json');

die(json_encode($res));
