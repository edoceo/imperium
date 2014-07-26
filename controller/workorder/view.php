<?php

$id = intval($_GET['w']);

$this->WorkOrder = new WorkOrder($id);

// Record Being Sent
if ( (!empty($this->_s->ReturnFrom)) && ($this->_s->ReturnFrom == 'mail') ) {
	unset($this->_s->ReturnFrom);
	if ($this->_s->ReturnCode == 200) {
		$msg = 'Work Order #' . $this->WorkOrder->id . ' sent to ' . $this->_s->EmailSentMessage->to;
		unset($this->_s->EmailSentMessage->to);
		Base_Diff::note($this->WorkOrder,$msg);
		$this->_s->info[] = $msg;
	}
	unset($this->_s->ReturnCode);
}

// Adding a Contact?
//if ( ($_POST['c'] == 'Add') && (!empty($_POST['add_contact_id'])) ) {
//	$sql = 'INSERT INTO workorder_contact (workorder_id,contact_id) VALUES (%d,%d)';
//	$this->_d->query(sprintf($sql,$this->WorkOrder->id,$_POST['add_contact_id']));
//	$this->_s->info[] = 'The Contact has been Added';
//	$this->_redirect('/workorder/view?w=' . $this->WorkOrder->id);
//	break;
//}

if ($this->WorkOrder['id'] > 0) {

	$_ENV['title'] = array('WorkOrder','View',"#$id");

	$this->Contact = new Contact($this->WorkOrder['contact_id']);

	// Show Notes
	$this->WorkOrderNoteList = $this->WorkOrder->getNotes();
	// Show Files
	$this->WorkOrderFileList = $this->WorkOrder->getFiles();
	// $this->ContactList = $this->Contact->getContactList();

	// Active Work Order Items
	//$where = array(
	//  'woi.status in (?)' => array(WorkOrderItem::STATUS_PENDING,'Active','Complete'),
	//  );
	$where = null;
	$this->WorkOrderItemList = $this->WorkOrder->getWorkOrderItems($where);

	// Show History Here
	//$where = array(
	//  'woi.status = ?' => array('Billed'),
	//  );
	//$this->WorkOrderItemHistoryList = $this->WorkOrder->getWorkOrderItems($where);
	// $this->WorkOrderHistoryList = $this->WorkOrder->getHistory();
	$_SESSION['WorkOrder'] = $this->WorkOrder;
}

// Work Order Jump List
// Add Prev / Next Links
$this->jump_list = array();
if ($this->WorkOrder['id'] > 0) {
	// Prev Five
	$s = sprintf('SELECT id FROM workorder where id < %d order by id desc limit 5',$this->WorkOrder['id']);
	$r = radix_db_sql::fetchAll($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = $x['id'];
	}
	// This
	$this->jump_list[] = $this->WorkOrder['id'];
	// Next Five
	$s = sprintf('SELECT id FROM workorder where id > %d order by id asc limit 5',$this->WorkOrder['id']);
	$r = radix_db_sql::fetchAll($s);
	foreach ($r as $x) {
		$this->jump_list[] = $x['id'];
	}
}
