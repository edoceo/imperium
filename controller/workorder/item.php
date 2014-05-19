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
	$this->WorkOrderItem['date'] = strftime('%Y-%m-%d');
	// Notify?
	if ($_ENV['aorkorder']['notify_send']) {
		$c = new Contact($this->WorkOrder['contact_id']);
		$this->WorkOrderItem['notify'] = $c->email;
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
	  'name','note','status'); // ,'notify' ? Gone?
	foreach ($set as $x) {
		$woi[$x] = trim($_POST[$x]);
	}
	$woi = $wo->addWorkOrderItem($woi);

	// Save to DB
	if ($id) {
		radix_session::flash('info', "Work Order Item #$id saved");
	} else {
		$id = $woi['id'];
		radix_session::flash('info', "Work Order Item #$id created");
	}
	$wo->save();

	// If Notify!
	if (!empty($_POST['notify'])) {

		$mail = array();
		$mail['rcpt'] = radix_filter::email($_POST['notify']);
		$mail['subj'] = 'Work Order #' . $wo['id'] . ' Update Notification';

		// Template
		$file = APP_ROOT . '/approot/etc/workorder-item-mail.txt';
		if (is_file($file)) {
			$body = file_get_contents($file);
		} else {
			radix_session::flash('warn', 'Work Order Item Notification Template is missing');
			$body = "New Work Order Item\n";
			$body.= "Work Order: \$wo_id\n";
			$body.= "Item: \$wi_name\n";
			$body.= "Cost: \$wi_quantity @ \$wi_rate/\$wi_unit \n";
			$body.= "Status: \$wi_status\n";
		}

		$body = str_replace('$wo_id',$wo['id'],$body);
		$body = str_replace('$wo_note',$wo['note'],$body);
		$body = str_replace('$wo_open_amount',$wo['open_amount'],$body);

		$body = str_replace('$wi_date', $woi['date'], $body);
		$body = str_replace('$wi_kind', $woi['kind'], $body);
		$body = str_replace('$wi_name', $woi['name'], $body);
		$body = str_replace('$wi_note', $woi['note'], $body);
		$body = str_replace('$wi_quantity', $woi['a_quantity'],$body);
		$body = str_replace('$wi_rate', $woi['a_rate'], $body);
		$body = str_replace('$wi_unit', $woi['a_unit'], $body);
		$body = str_replace('$wi_cost', sprintf('%0.4f', $woi['a_quantity'] * $woi['a_rate']), $body);
		$body = str_replace('$wi_status', $woi['status'], $body);

		$mail['body'] = $body;

		$_SESSION['mail-compose'] = $mail;

		// Want to Add This History
		$_SESSION['return-path'] = '/workorder/view?w=' . $wo['id'];
		radix::redirect('/email/compose');

	}

	radix::redirect('/workorder/view?w=' . $wo['id']);

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

