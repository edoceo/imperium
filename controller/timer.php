<?php
/**
 * Timer Controller
 *
 * @copyright	2008 Edoceo, Inc
 * @package    edoceo-imperium
 * @link       http://imperium.edoceo.com
 * @since      File available since Release 1013
 */

use Edoceo\Radix;
use Edoceo\Radix\Session;

if ('POST' == $_SERVER['REQUEST_METHOD']) {

	switch ($_POST['a']) {
	case 'timer-delete':

		$t = $_POST['timer-id'];
		unset($_SESSION['timer'][$t]);

		Session::flash('fail', 'Timer Deleted');

		Radix::redirect();

		break;

	case 'timer-save':

		$t = new \stdClass();
		$t->name = trim($_POST['timer-name']);
		$t->hash = sha1($t->name);

		if (empty($_SESSION['timer'])) {
		  $_SESSION['timer'] = [];
		}

		if (empty($_SESSION['timer'][$t->hash])) {
			$t->time_alpha = new \DateTime();
			$_SESSION['timer'][$t->hash] = $t;
		}

		Session::flash('info', 'Timer Created');

		Radix::redirect();

		break;

	case 'timer-stop':

		$tid = $_POST['timer-id'];
		$t = $_SESSION['timer'][$tid];
		if ( ! empty($t)) {
			$t->time_omega = new \DateTime();
		}
		$_SESSION['timer'][$t->id] = $t;

		Session::flash('info', 'Timer stopped');

		Radix::redirect();

		break;


	}

}
