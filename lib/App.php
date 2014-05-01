<?php
/**
	@file
	@brief Application Static Class
*/

class App
{
	public static $db;

	static function load_config()
	{
		// App Defaults
		$_ENV = parse_ini_file(APP_ROOT . '/etc/boot.ini',true);
		$_ENV = array_change_key_case($_ENV);
		// radix::dump($_ENV);

		// Merge Local
		$x = APP_ROOT . '/etc/host.ini';
		if ( (is_file($x)) && (is_readable($x)) ) {
			$x = parse_ini_file($x,true);
			$x = array_change_key_case($x);
			$_ENV = array_merge_recursive($_ENV,$x);
			// Zend_Debug::dump($_ENV);
		}
		// radix::dump($_ENV);

		// Merge Enviroment
		if ($x = getenv('IMPERIUM_CONFIG')) {
		    $x = APP_ROOT . '/etc/' . $x . '.ini';
		    if ( (is_file($x)) && (is_readable($x)) ) {
		        $x = parse_ini_file($x,true);
		        $x = array_change_key_case($x);
		        $_ENV = array_merge_recursive($_ENV,$x);
		    }
		}
		// radix::dump($_ENV);

		// Reduce to Singular Values
		foreach ($_ENV as $k0=>$opt) {
		    foreach ($opt as $k1=>$x) {
		        if (is_array($_ENV[$k0][$k1])) {
		            $_ENV[$k0][$k1] = array_pop($_ENV[$k0][$k1]);
		        }
		    }
		}
		// radix::dump($_ENV);

		ini_set('date.timezone',$_ENV['application']['zone']);
		date_default_timezone_set($_ENV['application']['zone']);

	}

	/**
		Adds and Item to the MRU
	*/
	static function addMRU($x)
	{
		if (empty($_SESSION['mru'])) $_SESSION['mru'] = array();
		if (!is_array($_SESSION['mru'])) $_SESSION['mru'] = array();

		if (empty($_SESSION['mru-list'])) $_SESSION['mru-list'] = array();
		if (!is_array($_SESSION['mru-list'])) $_SESSION['mru-list'] = array();

		// Remove Tailing Items
		while (count($_SESSION['mru']) > 5) { // $_SESSION['mru-max']) {
			array_pop($_SESSION['mru']);
		}

		// $key_list = array_keys($_SESSION['mru']);
		$key = md5(serialize($x));
		// if (in_array($key, $_SESSION['mru-list'])) {
		// 	// array_unshift($_SESSION['mru-list'], $key);
		// 	// unset($_SESSION['mru-list'][$key]);
		// 	// unset($_SESSION['mru'][$key]);
		// }

		if (is_object($x)) {
			array_unshift($_SESSION['mru'], $x);
		} elseif (is_array($x)) {
			// array_unshift($_SESSION['mru'], $x);
		} elseif (is_string($x)) {
			// array_unshift($_SESSION['mru-list'], $key);
			// array_unshift($key_list, $key);
			$_SESSION['mru'][$key] = $x;
		}

		// $_SESSION['mru-list'] = $key_list;

	}
	
	static function sendMail($rcpt, $mail)
	{
		$uri = parse_url($_ENV['mail']['smtp']);

		$mail = str_replace('%head_from%', sprintf('"%s" <%s>', $_ENV['company']['name'], $_ENV['mail']['from']), $mail);
		$mail = str_replace('%head_hash%', md5(openssl_random_pseudo_bytes(256)) . '@' . parse_url($_ENV['application']['base'], PHP_URL_HOST), $mail);

		require_once('Radix/mail/smtp.php');
		$smtp = new radix_mail_smtp(sprintf('%s://%s:%d', $uri['scheme'], $uri['host'], $uri['port']));

		$res = $smtp->ehlo($_ENV['application']['host']);
		print_r($res);
		$res = $smtp->auth($uri['user'], $uri['pass']);
		print_r($res);
		$res = $smtp->mailFrom($uri['user']);
		print_r($res);
		$res = $smtp->rcptTo($rcpt);
		print_r($res);
		$res = $smtp->data($mail);
		if (250 != $res[0]['code']) {
			throw new Exception("Could not send mail: {$res[0]['code']} {$res[0]['text']}");
		}
		// print_r($res);
		// $res = $smtp->quit();
		// print_r($res);
		if ($res[0]['code'] != 221) {
			print_r($res);
		}
	}


}