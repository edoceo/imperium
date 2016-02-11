<?php
/**
	@file
	@brief Convert WorkOrder to Invoice
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

$wo = new WorkOrder(intval($_GET['w']));

$_ENV['title'] = array("WorkOrder #{$wo['id']}", 'Build Invoice');

switch (strtolower($_POST['cmd'])) {
case 'invoice':

	$iv = $wo->toInvoice($_POST['invoice_id']);
	$x = $iv->getInvoiceItems();

	$msg = sprintf('Invoice #%d created from Work Order #%d with %d items', $iv['id'], $wo['id'], count($x));
	Session::flash('info', $msg);
	Radix::redirect('/invoice/view?i=' . $iv['id']);

	break;
}


$this->WorkOrder = $wo;

$w = array('woi.status in (?)' => array('Active','Complete'));
$w = null;
$this->WorkOrderItemList = $wo->getWorkOrderItems($w);
$this->Contact = new Contact($wo['contact_id']);

$this->InvoiceList = array(0 => '- New -');

$sql = 'SELECT * FROM invoice WHERE contact_id = ? AND status = ?';
$arg = array($this->Contact['id'], 'Active');
$res = SQL::fetch_all($sql, $arg);

foreach ($res as $x) {
	$k = $x['id'];
	$v = 'Invoice #' . $x['id'];
	$v.= ' from ' . date('m/d/y',strtotime($x['date']));
	$this->InvoiceList[$k] = $v;
}
