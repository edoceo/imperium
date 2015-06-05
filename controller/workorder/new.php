<?php
/**
	Work Order Create Action
	Create a new Work Order
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

$_ENV['title'] = array('WorkOrder','Create');
$this->WorkOrder = new WorkOrder(null);

if (!empty($_GET['c'])) {
	$c = new Contact(intval($_GET['c']));
	if (!empty($c['id'])) {
		$this->WorkOrder['contact_id'] = $c['id'];
		$this->Contact = $c;
		if (!empty($c->contact)) {
			$this->WorkOrder['requester'] = $c['contact'];
		}
		// @todo Should be getSubContacts()
		$this->ContactList = $this->Contact->getContactList();
		// $this->view->ContactAddressList = $db->fetchPairs("select id,address from contact_address where contact_id={$id}");
	}
}

Radix::$path = '/workorder/view';
