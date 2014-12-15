<?php
/**
	Create / Edit / Contact Details
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\Session;

$_ENV['title'] = array('Contact', 'Channel', 'View');

if ('create' == $_GET['a']) {
	unset($_GET['id']);
}

$this->ContactChannel = new ContactChannel($_GET['id']);
$this->Contact = new Contact($this->ContactChannel['contact_id']);
if (!empty($_GET['c'])) {
	$this->Contact = new Contact($_GET['c']);
	$this->ContactChannel['contact_id'] = $this->Contact['id'];
}

$action = strtolower($_POST['a']);
switch ($action) {
case 'save':
	foreach (array('kind','name','data') as $x) {
		$this->ContactChannel[$x] = $_POST[$x];
	}

	// Save to DB
	if (empty($this->ContactChannel['id'])) {
		$this->ContactChannel['auth_user_id'] = $_SESSION['uid'];
		$this->ContactChannel->save();
		Session::flash('info', 'Contact Channel saved');
	} else {
		$this->ContactChannel->save();
		Session::flash('info', 'Contact Channel created');
	}

	Radix::redirect('/contact/view?c=' . $this->Contact['id']);
	break;
case 'cancel':
	Radix::redirect('/contact/view?c=' . $this->Contact['id']);
case 'delete':
	$this->ContactChannel->delete();
	Session::flash('info', 'Contact Channel #' . $id . ' was deleted');
	Radix::redirect('/contact/view?c=' . $this->Contact['id']);
	break;
}

// Save Primary if Indicated
/*
if ($this->data['ContactChannel']['primary'] == 1)
{
	$this->Contact->id = $this->Session->read('Contact.id');
	if ($this->data['ContactChannel']['kind'] == ContactChannel::PHONE)
		$this->Contact->saveField('Contact.phone',$this->data['ContactChannel']['data'],false);
	if ($this->data['ContactChannel']['kind'] == ContactChannel::EMAIL)
		$this->Contact->saveField('Contact.email',$this->data['ContactChannel']['data'],false);
}
*/
