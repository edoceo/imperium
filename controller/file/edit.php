<?php
/**
	Create a Note
*/

$_ENV['title'] = array('File','Create');

$this->File = new Base_File();

// Linked to Object?
if ($_GET['l'] == 'r') {
	$url = parse_url($_SERVER['HTTP_REFERER']);
	parse_str($url['query'],$arg);
	$_GET = $arg;
}

if (!empty($_GET['c'])) {
	$this->File['link'] = sprintf('contact:%d', $_GET['c']);
}
if (!empty($_GET['i'])) {
	$this->File['link'] = sprintf('invoice:%d', $_GET['i']);
}
if (!empty($_GET['w'])) {
	$this->File['link'] = sprintf('workorder:%d', $_GET['w']);
}

if (!empty($this->File['link'])) {
	$_ENV['title'] = array('File','Create',' #' . $this->File['link']);
}
