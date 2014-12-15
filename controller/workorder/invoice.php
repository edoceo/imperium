<?php
/**
	@file
	@brief Convert WorkOrder to Invoice
*/

$wo = new WorkOrder(intval($_GET['w']));

$_ENV['title'] = array("WorkOrder #{$wo['id']}", 'Build Invoice');

switch (strtolower($_POST['cmd'])) {
case 'invoice':

	// if ($wo->status != 'Active') {
	//     $this->_s->fail[] = 'Only Active WorkOrders may build an Invoice';
	//     $this->redirect('/workorder/view?w=' . $id);
	// }

	// $this->_d->beginTransaction();
	$iv = $wo->toInvoice($_POST['invoice_id']);
	$x = $iv->getInvoiceItems();

	$msg = sprintf('Invoice #%d created from Work Order #%d with %d items', $iv['id'], $wo['id'], count($x));
	// Base_Diff::note($wo,$this->_s->info);
	// Base_Diff::note($iv,$this->_s->info);
	Session::flash('info', $msg);
	// $this->_d->commit();
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
