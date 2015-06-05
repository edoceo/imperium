<?php
/**
	Save an Image Captured for a Contact
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\Session;

if (empty($_GET['c'])) {
	return(0);
}

$c = new Contact(intval($_GET['c']));
if (empty($c['id'])) {
	Session::flash('fail', 'Contact not found');
	Radix::redirect('/contact');
}

$this->Contact = $c;

switch ($_POST['a']) {
case 'save':

	// Radix::dump($_POST);

	$image = trim($_POST['image-data']);

	if ('data:image/jpeg;base64,' == substr($image, 0, 23)) {
		$b64 = substr($image, 22);
		// Radix::dump($b64);
		$jpg_data = base64_decode($b64);
		$dir = sprintf('%s/webroot/img/content/contact/%u', APP_ROOT, $this->Contact['id']);
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$jpg_file = sprintf('%s/0.jpg', $dir);
		$res = file_put_contents($jpg_file, $jpg_data);
		if (0 == $res) {
			Session::flash('fail', 'Unable to write the snapshot');
		}
	}

	if ('data:image/png;base64,' == substr($image, 0, 22)) {
		$b64 = substr($image, 22);
		Radix::dump($b64);
		$png_data = base64_decode($b64);
		$dir = sprintf('%s/webroot/img/content/contact/%u', APP_ROOT, $this->Contact['id']);
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$png_file = sprintf('%s/0.png', $dir);
		$res = file_put_contents($png_file, $png_data);
		if (0 == $res) {
			Session::flash('fail', 'Unable to write the snapshot');
		}
	}

	Session::flash('info', 'Capture Saved');
	Radix::redirect('/contact/view?c=' . $this->Contact['id']);

}
