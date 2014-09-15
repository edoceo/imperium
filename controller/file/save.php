<?php
/**
	FileController createAction
*/

$f = new Base_File($_GET['id']);

// Linked to Object?
// if ($_GET['l'] == 'r') {
// 	$url = parse_url($_SERVER['HTTP_REFERER']);
// 	parse_str($url['query'],$arg);
// 	$_GET = $arg;
// }
// 
// if (!empty($_GET['c'])) {
// 	$f->link = sprintf('contact:%d',$_GET['c']);
// }
// if (!empty($_GET['i'])) {
// 	$f->link = sprintf('invoice:%d',$_GET['i']);
// }
// if (!empty($_GET['w'])) {
// 	$f->link = sprintf('workorder:%d',$_GET['w']);
// }
// 
// if (!empty($f->link)) {
// 	$this->view->title = array('File','Create',' #' . $f->link);
// }

$f = new Base_File($_POST['id']);
switch (strtolower($_POST['a'])) {
case 'delete':
	$f->delete();
	radix_session::flash('info', 'File Deleted');
	radix::redirect('/file');
	break;
case 'upload':
	$file = $_FILES['file'];
	$f = Base_File::copyPost($file);
	if ($f) {
		$bf->id = intval($_POST['id']);
		$f['link'] = $_POST['link'];
		$f->import($_FILES['file']);
		$f->save();
		radix_session::flash('info', 'File #' . $f->id . ' Saved');
	} else {
		radix_session::flash('fail', 'Invalid Input');
	}
}

// Redirect Out
if (!empty($f['link'])) {
	if (preg_match('/(contact|invoice|workorder):(\d+)/', $f['link'], $m)) {
		$page = '/' . $m[1] . '/view?' . substr($m[1],0,1) . '=' . $m[2];
		radix::redirect($page);
	}
}

radix::redirect('/file');

