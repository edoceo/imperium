<?php
/**
	Create a new Contact
*/

namespace Edoceo\Imperium;

use Radix;

$_ENV['title'] = array('Contact', 'Create');
$this->Contact = new Contact(null);

if (!empty($_GET['parent'])) {
	$c = new Contact(intval($_GET['parent']));
	if (!empty($c['id'])) {
		$this->Contact['parent_id'] = $c['id'];
	}
}

Radix::$path = '/contact/view';
