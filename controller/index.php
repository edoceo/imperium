<?php
/**
	@file
	@brief 
*/

$_ENV['title'] = 'Dashboard: ' . date('Y-m-d');

$sql_w = 'SELECT workorder.*, b.name AS contact_name ';
$sql_w.= ' FROM workorder ';
$sql_w.= ' JOIN contact b ON workorder.contact_id=b.id ';
$sql_w.= ' JOIN base_enum ON workorder.kind = base_enum.name ';
$sql_w.= " WHERE workorder.status in ('Active','Pending') ";
$sql_w.= ' ORDER BY base_enum.sort, workorder.status, workorder.date desc, workorder.id DESC';

// Pending Work Order Items
$sql_woi = 'SELECT workorder.*, contact.name AS contact_name ';
$sql_woi.= ' FROM workorder ';
$sql_woi.= ' JOIN contact on workorder.contact_id = contact.id ';
$sql_woi.= ' JOIN workorder_item ON workorder.id = workorder_item.workorder_id ';
$sql_woi.= ' JOIN base_enum ON workorder.kind = base_enum.name ';
$sql_woi.= " WHERE workorder.status = 'Active' AND workorder_item.status = 'Pending' ";
$sql_woi.= ' ORDER BY base_enum.sort, workorder.status, workorder.date desc, workorder.id DESC';

$data = array(
	// 'Active Timers' => array(
	// 	'css' => 'index_pack',
	// 	'list' => Timer::activeList(),
	// 	'view' => '../elements/timer-list.phtml'),
	'Pending Work Order Items' => array(
		'css' => 'index_pack',
		'list' => App::$db->fetch_all($sql_woi),
		'view' => 'workorder-list'),
	'Active Work Orders' => array(
		'css' => 'index_list',
		'list' => App::$db->fetch_all($sql_w),
		'view' => 'workorder-list'),
	'Active Invoices' => array(
		'css' => 'index_list',
		'list' => App::$db->fetch_all("select invoice.*,b.name as contact_name from invoice join contact b on invoice.contact_id=b.id where ((invoice.paid_amount is null or invoice.paid_amount < invoice.bill_amount) and invoice.status in ('Active','Sent','Hawk')) order by invoice.date desc, invoice.id desc"),
		'view' => 'invoice-list'),
);
/*
$this->paginate = array(
'WorkOrder' => array(
	'conditions' => 'WorkOrder.status_id in (100,200)',
	'limit'=>50,
	'order' => array('WorkOrder.id'=>'desc','WorkOrder.date'=>'desc'),
	'page'=>1,
	'recursive'=>1,
	),
'Invoice' => array(
	'conditions' => '((Invoice.paid_amount is null or Invoice.paid_amount<Invoice.bill_amount) and Invoice.status_id in (100,200))',
	'limit'=>50,
	'order' => array('Invoice.date'=>'desc'),
	'page'=>1,
	'recursive'=>0,
	),
);
*/
// unset($this->SearchTerm);
// $this->_s->ReturnTo = '/';

$_ENV['data'] = $data;
