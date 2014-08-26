<?php
/**
	@file
	@brief Save a Contact
*/

$id = intval($_GET['c']);

// Delete Requested?
switch (strtolower($_POST['a'])) {
case 'create-account':

	$c = new Contact($id);

	$a = new Account();
	$a->kind = 'Sub: Customer';
	$a->code = $id;
	$a->name = $c->name;
	$a->parent_id = $_ENV['account']['contact_ledger_container_id'];
	$a->active = 't';
	$a->link_to = 'contact';
	$a->link_id = $id;
	$a->save();

	$c->account_id = $a->id;
	$c->save();

	$this->redirect('/contact/view?c=' . $id);

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

	$c = new Contact($id);
	$c->delete();
	radix_session::flash('info', 'Contact #' . $id . ' was deleted');
	radix::redirect('/contact');

case 'save':

	$co = new Contact($id);
	$co['auth_user_id'] = $_SESSION['uid'];
	$co['account_id']  = intval($_POST['account_id']);
	$co['parent_id']  = null;
	$co['kind']    = $_POST['kind'];
	$co['status']  = $_POST['status'];
	$co['contact'] = $_POST['contact'];
	$co['company'] = $_POST['company'];
	$co['title'] = $_POST['title'];
	$co['email'] = $_POST['email'];
	$co['phone'] = $_POST['phone'];
	$co['url'] = $_POST['url'];
	$co['tags'] = $_POST['tags'];

	$co->save();

	if ($id) {
		radix_session::flash('info', "Contact #$id saved");
	} else {
		$id = $co['id'];
		radix_session::flash('info', "Contact #$id created");
	}

	radix::redirect('/contact/view?c=' . $id);
}
