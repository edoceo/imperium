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

		// Merge Local
		$x = APP_ROOT . '/etc/host.ini';
		if ( (is_file($x)) && (is_readable($x)) ) {
			$x = parse_ini_file($x,true);
			$x = array_change_key_case($x);
			$_ENV = array_merge_recursive($_ENV,$x);
		}

		// Merge Enviroment
		if ($x = getenv('IMPERIUM_CONFIG')) {
			$x = APP_ROOT . '/etc/' . $x . '.ini';
			if ( (is_file($x)) && (is_readable($x)) ) {
				$x = parse_ini_file($x,true);
				$x = array_change_key_case($x);
				$_ENV = array_merge_recursive($_ENV,$x);
			}
		}

		// Reduce to Singular Values
		foreach ($_ENV as $k0=>$opt) {
			foreach ($opt as $k1=>$x) {
				if (is_array($_ENV[$k0][$k1])) {
					$_ENV[$k0][$k1] = array_pop($_ENV[$k0][$k1]);
				}
			}
		}

		ini_set('date.timezone',$_ENV['application']['zone']);
		date_default_timezone_set($_ENV['application']['zone']);

	}

	/**
		Adds and Item to the MRU
	*/
	static function addMRU($l, $h)
	{
		if (empty($_SESSION['mru'])) $_SESSION['mru'] = array();
		if (!is_array($_SESSION['mru'])) $_SESSION['mru'] = array();

		$key = md5($l);
		if (!empty($_SESSION['mru'][$key])) {
			unset($_SESSION['mru'][$key]);
		}

		// Remove Tailing Items
		while (count($_SESSION['mru']) >= 5) { // $_SESSION['mru-max']) {
			array_shift($_SESSION['mru']);
		}

		// Add the new one (reverse on display
		$_SESSION['mru'][$key] = array(
			'link' => $l,
			'html' => $h,
		);

	}

}
