<?php
/**
	Item Action handles requests to create, view and save an Item
*/

$_ENV['title'] = array('Work Order','Item');

$mode = 'create';
$x = intval($_GET['w']);
if (!empty($x)) {
	$mode = 'create';
}
$x = intval($_GET['id']);
if (!empty($x)) {
	$mode = 'view';
}
if (count($_POST)) {
	$mode = 'save';
}

switch ($mode) {
case 'create':
	$_ENV['title'] = array('Work Order','Item','Create');
	$this->WorkOrder = new WorkOrder(intval($_GET['w']));
	$this->WorkOrderItem = $this->WorkOrder->newWorkOrderItem();
	// Notify?
	if ($_ENV['aorkorder']['notify_send']) {
		$c = new Contact($this->WorkOrder->contact_id);
		$this->WorkOrderItem->notify = $c->email;
	}
	break;

case 'save':

	$id = intval($_GET['id']);

	// Delete Request?
	if ($_POST['c'] == 'Delete') {
		$woi = new WorkOrderItem($id);
		$woi->delete();
		radix_session::flash('info', 'Work Order Item #' . $id . ' was deleted');
		radix::redirect('/workorder/view?w=' . $woi['workorder_id']);
	}

	// Save Request
	$wo = new WorkOrder($_POST['workorder_id']);
	$woi = new WorkOrderItem($id);
	$set = array(
	  'kind','date','time_alpha','time_omega',
	  'e_rate','e_quantity','e_unit','e_tax_rate',
	  'a_rate','a_quantity','a_unit','a_tax_rate',
	  'name','note','status','notify');
	foreach ($set as $x) {
		$woi->$x = trim($_POST[$x]);
	}
	$woi = $wo->addWorkOrderItem($woi);

	// Save to DB
	if ($id) {
		radix_session::flash('info', "Work Order Item #$id saved");
	} else {
		$id = $woi->id;
		radix_session::flash('info', "Work Order Item #$id created");
	}
	$wo->save();

	// If Notify!
	if (!empty($_POST['notify'])) {

		$this->_s->EmailComposeMessage = new stdClass();
		$this->_s->EmailComposeMessage->to = $_POST['notify'];
		$this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' Update Notification';

		// Template
		$file = APP_ROOT . '/approot/etc/workorder-item-mail.txt';
		if (is_file($file)) {
			$body = file_get_contents($file);
		}

		$body = str_replace('$wo_id',$wo->id,$body);
		$body = str_replace('$wo_note',$wo->note,$body);
		$body = str_replace('$wo_open_amount',$wo->open_amount,$body);

		$body = str_replace('$wi_date',$woi->date,$body);
		$body = str_replace('$wi_kind',$woi->kind,$body);
		$body = str_replace('$wi_name',$woi->name,$body);
		$body = str_replace('$wi_note',$woi->note,$body);
		$body = str_replace('$wi_quantity',$woi->a_quantity,$body);
		$body = str_replace('$wi_rate',$woi->a_rate,$body);
		$body = str_replace('$wi_unit',$woi->a_unit,$body);
		$body = str_replace('$wi_status',$woi->status,$body);

		$this->_s->EmailComposeMessage->body = $body;

		// Want to Add This History
		$this->_s->ReturnTo = '/workorder/view?w=' . $wo->id;
		$this->redirect('/email/compose');

	}

	$this->redirect('/workorder/view?w=' . $wo->id);

	break;

case 'view':

	$id = intval($_GET['id']);
	$woi = new WorkOrderItem($id);
	if (empty($woi->id)) {
		$this->_s->fail[] = sprintf('Cannot find Work Order Item #%d',$id);
		return;
	}

	$this->WorkOrder = new WorkOrder($woi->workorder_id);
	$this->WorkOrderItem = $woi;

	break;
}

