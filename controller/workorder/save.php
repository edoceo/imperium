<?php
/**
	Save Work Order
*/


$id = intval($_GET['w']);
$wo = new WorkOrder($id);

switch (strtolower($_POST['a'])) {
case 'bill':
	radix::redirect('/workorder/invoice?w=' . $id);
	// $this->invoiceAction();
	// $this->_billAction();
	break;
case 'close':
	$sql = "UPDATE workorder_item SET status = 'COMPLETE' ";
	$sql.= sprintf('WHERE workorder_id = %d',$wo->id);
	$this->_d->query($sql);
	$wo->status = 'Closed';
	$wo->save();
	radix_session::flash('info', "Work Order #$id Closed");
	radix::redirect(sprintf('/workorder/view?w=%d', $wo->id));
case 'delete':
	$wo->delete();
	radix_session::flash('info', "Work Order #$id was deleted");
	radix::redirect('/workorder');
	break;
case 'send':

	$co = new Contact($wo->contact_id);

	// Make a Key
	$ah = Auth_Hash::make($wo);

	$this->_s->EmailComposeMessage = new stdClass();
	$this->_s->EmailComposeMessage->to = $co->email;
	$this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' from ' . $this->_c->Company->name;

	// Load Template File
	$file = APP_ROOT . '/approot/etc/workorder-mail.txt';
	if (is_file($file)) {
		$this->_s->EmailComposeMessage->body = file_get_contents($file);
	}
	// $this->_s->EmailComposeMessage->RecipientList[''] = '- none -';
	// $this->_s->EmailComposeMessage->RecipientList+= $co->getEmailList();
	// $this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' from ' . $this->_c->Company->name;
	// $this->_s->EmailComposeMessage->body = "Hello $contact,\n";
	// $this->_s->EmailComposeMessage->body.= '  A link to your recent Work Order is included below.';
	// $this->_s->EmailComposeMessage->body.= ' This is to inform you of the work performed, please retain a copy for your records.';
	// $this->_s->EmailComposeMessage->body.= "\n\n";
	// $this->_s->EmailComposeMessage->body.= "Work Order #" . $wo->id . "\n  " . AppTool::baseUri() . '/hash/' . $ah['hash'] . "\n";
	// $this->_s->EmailComposeMessage->body.= "\n";
	// $this->_s->EmailComposeMessage->body.= 'Thank you for your continued business.';
	// $this->_s->EmailComposeMessage->body.= "\n\nSincerely,\n";
	// $this->_s->EmailComposeMessage->body.= '  ' . $this->_c->Company->name . "\n\n";
	// $this->_s->EmailComposeMessage->body.= 'PS: The linked files are in Adobe PDF format. You must have Acrobat Reader (or other compatible software) installed to view these documents.';

	// $this->_s->ReturnTo = sprintf('/workorder/view/id/%d?sent=true',$wo->id);
	$this->_s->ReturnGood = sprintf('/workorder/view?w=%d?sent=good',$wo->id);
	$this->_s->ReturnFail = sprintf('/workorder/view?w=%d?sent=fail',$wo->id);
	radix::redirect('/email/compose');
	break;
case 'save':

	$list = array('contact_id','date','kind','base_rate','base_unit','requester','note');
	foreach ($list as $x) {
		$wo[$x] = trim($_POST[$x]);
	}
	$wo->save();

	if ($id) {
		radix_session::flash('info', "Work Order #$id saved");
	} else {
		$id = $wo['id'];
		radix_session::flash('info', "Work Order #$id created");
	}
	radix::redirect('/workorder/view?w=' . $id);
	break;
case 'void':
	$sql = 'UPDATE workorder_item SET status = ? WHERE workorder_id = ? AND status = ?';
	radix_db_sql::query($sql, array('Void', $wo->id, 'Pending'));
	$wo['status'] = 'Void';
	$wo->save();
	radix_session::flash('info', "Work Order #{$wo->id} voided");
	radix::redirect('/');
	break;

}
