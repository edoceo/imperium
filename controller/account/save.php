<?php
/**

*/

use Edoceo\Radix;
use Edoceo\Radix\Session;

use Edoceo\Imperium\Account;

$a = new Account($_POST['id']);

switch (strtolower($_POST['a'])) {
case 'delete':
	$a->delete();
	Session::flash('info', "Account #{$a['id']} deleted");;
	break;
case 'save':

	$a['parent_id'] = $_POST['parent_id'];
	$a['account_tax_line_id'] = $_POST['account_tax_line_id'];
	$a['code'] = $_POST['code'];
	$a['kind'] = $_POST['kind'];
	$a['name'] = $_POST['name'];
	$a['bank_account'] = $_POST['bank_account'];
	$a['bank_routing'] = $_POST['bank_routing'];
	$a->save();

	Session::flash('info', "Account #{$a['id']} saved");
}

$r = '/account';
if (!empty($_POST['r'])) {
	$r = $_POST['r'];
}

Radix::redirect($r);
