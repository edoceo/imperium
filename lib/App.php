<?php
/**
	@file
	@brief Application Static Class
*/

namespace Edoceo\Imperium;

class App
{
	public static $db;

	static function load_config()
	{
		// App Defaults
		$_ENV = parse_ini_file(APP_ROOT . '/etc/boot.ini',true);
		$_ENV = array_change_key_case($_ENV);
		// Radix::dump($_ENV);

		// Merge Local
		$x = APP_ROOT . '/etc/host.ini';
		if ( (is_file($x)) && (is_readable($x)) ) {
			$x = parse_ini_file($x,true);
			$x = array_change_key_case($x);
			$_ENV = array_merge_recursive($_ENV,$x);
			// Zend_Debug::dump($_ENV);
		}
		// Radix::dump($_ENV);

		// Merge Enviroment
		if ($x = getenv('IMPERIUM_CONFIG')) {
			$x = APP_ROOT . '/etc/' . $x . '.ini';
			if ( (is_file($x)) && (is_readable($x)) ) {
				$x = parse_ini_file($x,true);
				$x = array_change_key_case($x);
				$_ENV = array_merge_recursive($_ENV,$x);
			}
		}
		// Radix::dump($_ENV);

		// Reduce to Singular Values
		foreach ($_ENV as $k0=>$opt) {
			foreach ($opt as $k1=>$x) {
				if (is_array($_ENV[$k0][$k1])) {
					$_ENV[$k0][$k1] = array_pop($_ENV[$k0][$k1]);
				}
			}
		}
		// Radix::dump($_ENV);

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

}