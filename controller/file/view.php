<?php
/**
    @file
    @brief File Controller
*/

$id = intval($_GET['id']);
// if (empty($id)) {
// 	throw new Exception('Bad Request', 400);
// }

$f = new Base_File($id);
if (empty($f['id'])) {
	throw new Exception('File Not Found', 404);
}

$_ENV['title'] = array('File', $f['name']);
$this->File = $f;
$this->FileHistoryList = $f->getHistory();
