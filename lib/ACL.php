<?php
/**

*/

class ACL
{

	static function may($path, $verb=null)
	{
		if (null == $verb) $verb = 'GET';

		if (empty($_SESSION['uid'])) {
			switch ($path) {
			case '/auth/sign-in':
				return true;
			}
			return false;
		}

		// Exact Match?
		if (!empty($_SESSION['_acl'][$path][$verb])) {
			return true;
		}

		// Pattern Match
		foreach ($_SESSION['_acl'] as $p=>$acl) {
			if (fnmatch($p, $path)) {
				if (true == $acl[$verb]) {
					// Set Specific Path in _acl Cache
					return self::permit($path, $verb);
				}
			}
		}

		return false;
	}

	static function permit($path, $verb=null)
	{
		if (empty($_SESSION['_acl'][$path])) {
			$_SESSION['_acl'][$path] = array(
				'GET' => true,
				'POST' => false,
				'DELETE' => false,
			);
		}
		if (!empty($verb)) {
			$_SESSION['_acl'][$path][$verb] = true;
		}

		return true;
	}
}