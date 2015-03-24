<?php
/**
	Save a Note
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\Session;

// Radix::dump($_POST);

$n = new Base_Note(intval($_POST['id']));
if (empty($n['auth_user_id'])) {
	$n['auth_user_id'] = $_SESSION['uid'];
}

switch (strtolower($_POST['a'])) {
case 'delete':

	// @todo Check if IsAllowed
	$back_page = '/';
	$n->delete();
	Session::flash('info', 'Note #' . $id . ' was deleted');
	break;

case 'edit':

	Radix::redirect('/note/edit?id=' . $n['id']);
	break;

case 'save':

	$n['kind'] = $_POST['kind'];
	// Append on Conversation
	if ($n['kind'] == 'Conversation') {
		$data = trim($n['data']);
		$data.= "\n---\n# " . date('D \t\h\e jS \o\f F Y \a\t H:i') . "\n";
		$data.= $_POST['data'];
		$n['data'] = trim($data);
	} else {
		$n['data'] = trim($_POST['data']);
	}
	$x = str_replace(array('<br>','<br/>','<br />'), "\n", $n['data']);
	$x = trim(strip_tags($x));
	$n['name'] = strtok($x,"\n");
	$n['link'] = $_POST['link'];

	$n->save();

	if ($id) {
		Session::flash('info', "Note #$id saved");
	} else {
		$id = $n['id'];
		Session::flash('info', "Note #$id created");
	}

	$back_page = '/note/view?id=' . $id;
}

// Redirect Out
if (!empty($n['link'])) {
	if (preg_match('/(contact|invoice|workorder):(\d+)/',$n['link'],$m)) {
		$back_page = '/' . $m[1] . '/view?' . substr($m[1],0,1) . '=' . $m[2];
	}
}

Radix::redirect($back_page);
