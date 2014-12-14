<?php
/**

*/

$a = new Account($_POST['id']);

switch (strtolower($_POST['a'])) {
case 'delete':
	$a->delete();
	Radix\Session::flash('info', "Account #{$a['id']} deleted");;
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

	Radix\Session::flash('info', "Account #{$a['id']} saved");
}

radix::redirect('/account');