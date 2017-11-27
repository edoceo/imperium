<?php
/**
	@file
	@brief Save a Contact
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

use Edoceo\Imperium\Contact\Event;

$C = new Contact($_GET['c']);

// Delete Requested?
switch (strtolower($_POST['a'])) {
case 'capture':

	Radix::redirect('/contact/capture?c=' . $c['id']);

	break;

case 'create-account':

	$a = new Account();
	$a['kind'] = 'Customer Ledger';
	$a['code'] = $C['id'];
	$a['name'] = $C['name'];
	$a['parent_id'] = $_ENV['account']['client_ledger_id'];
	$a['active'] = 't';
	$a->save();

	$C['account_id'] = $a['id'];
	$C->save();

	Session::flash('fail', SQL::lastError());
	Session::flash('info', sprintf('Account #%d created', $a['id']));
	Radix::redirect('/account/edit?' . http_build_query(array(
		'id' => $a['id'],
		'r' => '/contact/view?c=' . $C['id']
	)));

	break;

case 'delete':

	/*
	$c_so = $this->WorkOrder->findCount('WorkOrder.contact_id=' . $id);
	$c_iv = $this->Invoice->findCount('Invoice.contact_id=' . $id);

	if ( (($c_so == 0) && ($c_iv == 0)) || ($this->Session->read('Contact.delete_confirm')==true) ) {

		$this->Contact->delete($id);

		$this->Session->setFlash('Client deleted');
		$this->Session->delete('Contact');

		$this->redirect(2);
	}

	$this->Session->setFlash("This Contact has $c_so " . Configure::read('WorkOrder.names') . " and $c_iv Invoices, are you sure you want to delete?",'default',null,'error');
	$this->Session->write('Contact.delete_confirm',true);
	$this->redirect('/contacts/view?c=' . $id);
	*/

	$C->delete();
	Session::flash('info', 'Contact #' . $C['id'] . ' deleted');
	Radix::redirect('/contact');

	break;

case 'ping':

	$ce = new Event();
	$ce['contact_id'] = $C['id'];
	$ce['cts'] = $_SERVER['REQUEST_TIME']; // Create Time
	$ce['xts'] = $_SERVER['REQUEST_TIME'] + (86400 * 4); // Alert Time
	$ce['name'] = 'Ping this Contact';
	$ce->save();

	Session::flash('info', 'Contact #' . $id . ' event added');
	Radix::redirect('/contact');

	break;

case 'save':

	$C['auth_user_id'] = $_SESSION['uid'];
	$C['kind']    = $_POST['kind'];
	$C['status']  = $_POST['status'];
	$C['contact'] = $_POST['contact'];
	$C['company'] = $_POST['company'];
	$C['parent_id'] = intval($_POST['parent_id']);
	$C['title'] = $_POST['title'];
	$C['email'] = $_POST['email'];
	$C['phone'] = $_POST['phone'];
	$C['url'] = $_POST['url'];
	$C['tags'] = $_POST['tags'];

	$C->save();

	Session::flash('info', "Contact #{$C['id']} saved");
	Radix::redirect('/contact/view?c=' . $C['id']);
}

// Radix::dump($_POST);
