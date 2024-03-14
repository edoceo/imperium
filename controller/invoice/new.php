<?php
/**
	Work Order Create Action
	Create a new Work Order
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

$_ENV['title'] = array('Invoice','Create');
$this->Invoice = new Invoice(null);

if (!empty($_GET['c'])) {
	$c = new Contact(intval($_GET['c']));
	if (!empty($c['id'])) {
		$this->Invoice['contact_id'] = $c['id'];
		$this->Contact = $c;
		$this->ContactList = $this->Contact->getContactList();
	}
}

$this->jump_list = [];
$this->InvoiceItemList = [];
$this->InvoiceTransactionList = [];

Radix::$path = '/invoice/view';
