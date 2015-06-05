<?php
/**

*/

function _api_log_request($add=null)
{
	$path = $_SERVER['REQUEST_URI'];
	if (strpos($path,'?')) {
		$path = substr($path,0,strpos($path,'?'));
	}
	$path = trim($path,'/');

	$file = sprintf('%s/var/%s.req', APP_ROOT, rawurlencode($path));
	$fh = fopen($file,'a');
	if (empty($fh)) {
		$fh = fopen(sprintf('/tmp/imperium-%s.log', rawurlencode($path)), 'a');
	}
	// if (empty($fh)) {
	// 	error_log(
	// }
	fwrite($fh,"{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}\n");
	// fwrite($fh, 'SERVER ' . print_r($_SERVER, true) . "\n");
	fwrite($fh,"HEADER\n");
	if (!empty($_SERVER['CONTENT_LENGTH'])) {
		fwrite($fh, "  Content-Length: {$_SERVER['CONTENT_LENGTH']}\n");
	}
	if (!empty($_SERVER['CONTENT_TYPE'])) {
		fwrite($fh, "  Content-Type: {$_SERVER['CONTENT_TYPE']}\n");
	} 
	foreach ($_SERVER as $k=>$v) {
		if ('HTTP_' == substr($k,0,5)) {
			$k = substr($k,5);
			$k = strtolower($k);
			$k = str_replace('_',' ',$k);
			$k = ucwords($k);
			$k = str_replace(' ','-',$k);
			fwrite($fh, "  $k: $v\n");
		}
	}
	// fwrite($fh, 'ADD ' . print_r($add, true) . "\n");
	// fwrite($fh, "POST\n");
	// fwrite($fh, file_get_contents('php://input'));
	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		fwrite($fh,'POST ' . print_r($_POST,true) . "\n");
	}
	fwrite($fh, "\n");
	fclose($fh);
}
