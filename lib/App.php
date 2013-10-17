<?php
/**

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
		            while (count($_ENV[$k0][$k1]) > 1) {
		                array_shift($_ENV[$k0][$k1]);
		            }
		            $_ENV[$k0][$k1] = $_ENV[$k0][$k1][0];
		        }
		    }
		}
		// radix::dump($_ENV);

		ini_set('date.timezone',$_ENV['application']['zone']);
		date_default_timezone_set($_ENV['application']['zone']);
	}
}