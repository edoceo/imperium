<?php
/**
    @file
    @brief File Controller
*/

$f = new Base_File(intval($_GET['id']));

if (!is_file($f['path'])) {
	Session::flash('fail', 'File not Found');
	Radix::redirect($_SERVER['HTTP_REFERER']);
}

// $this->force_download = true;
header('Cache-Control: private,must-revalidate,max-age=5',true);

//if (empty($this->force_download)) {
header('Content-Disposition: attachment; filename="' . $f['name'] . '"',true);
//} else {
//  header('Content-Disposition: inline; filename="' . $this->name . '"',true);
//}
header('Content-Length: ' . filesize($f['path']), true);
header('Content-Type: ' . $f['kind'], true);

$fh = fopen($f['path'], 'r');
fpassthru($fh);

exit(0);