<?php
/**

*/

$_ENV['title'] = array('Note','Create');

$n = new Base_Note();

// Linked to Object?
if ($_GET['l'] == 'r') {
	$url = parse_url($_SERVER['HTTP_REFERER']);
	parse_str($url['query'],$arg);
	$_GET = $arg;
}

if (!empty($_GET['c'])) {
	$n['link'] = sprintf('contact:%d',$_GET['c']);
}
if (!empty($_GET['i'])) {
	$n['link'] = sprintf('invoice:%d',$_GET['i']);
}
if (!empty($_GET['w'])) {
	$n['link'] = sprintf('workorder:%d',$_GET['w']);
}

if (!empty($n['link'])) {
	$_ENV['title'] = array('Note','Create',' #' . $n['link']);
}

$this->Note = $n;
// $this->render('edit');
