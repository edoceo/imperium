<?php
/**
	Save or otherwise Process the Invoice
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;

$Invoice = new Invoice(intval($_GET['i']));

switch (strtolower($_POST['a'])) {
case 'delete':
	$Invoice->delete();
	Session::flash('info', 'Invoice #' . $Invoice['id'] . ' was deleted');
	$this->redirect('/');
	break;
case 'hawk':
	$Invoice->setFlag(Invoice::FLAG_HAWK);
	$Invoice->save();
	Session::flash('info', 'Hawk monitoring has been added to this invoice, reminders will be according to cron schedule');
	$this->redirect('/invoice/view?i=' . $Invoice['id']);
	break;
case 'no hawk':
	$Invoice->delFlag(Invoice::FLAG_HAWK);
	$Invoice->save();
	Session::flash('info', 'Hawk monitoring has been removed from this invoice');
	$this->redirect('/invoice/view?i=' . $Invoice['id']);
	break;
case 'copy':

	// Copy Invoice
	$I_Copy = new Invoice();
	foreach (array('contact_id','requester','kind','status','base_rate','base_unit','bill_address_id','ship_address_id','note') as $x) {
		$I_Copy[$x] = $Invoice[$x];
	}
	$I_Copy->setFlag(Invoice::FLAG_OPEN);
	$I_Copy->save();

	// Copy Invoice Items
	$list = $Invoice->getInvoiceItems();
	foreach ($list as $II_Orig) {
		$II_Copy = new InvoiceItem(null);
		$II_Copy['invoice_id'] = $I_Copy['id'];
		foreach (array('quantity','rate','unit','name','note','tax_rate') as $x) {
			$II_Copy[$x] = $II_Orig[$x];
		}
		$II_Copy->save();
	}
	Radix::redirect('/invoice/view?i=' . $I_Copy['id']);

	break;

case 'paid':

	// New Transaction Holder
	$at = new \stdClass();
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry['date'] = date('Y-m-d');
	$at->AccountJournalEntry['note'] = 'Payment for Invoice #' . $Invoice['id'];
	$at->AccountLedgerEntryList = array();
	// @todo Detect if should be Inbound Cash or Account Rx

	// This is the Cash Based Method :(
//			// Inbound Cash
//			$a = new Account( $_ENV['account']['inbound_account_id'] );
//			$ale = new AccountLedgerEntry();
//			$ale->account_id = $a->id;
//			$ale->account_name = $a->full_name;
//			$ale->amount = abs($Invoice->bill_amount) * -1;
//			$at->AccountLedgerEntryList[] = $ale;
//			// Invoice Revenue
//			$a = new Account( $_ENV['account']['revenue_id'] );
//			$ale = new AccountLedgerEntry();
//			$ale->account_id = $a->id;;
//			$ale->account_name = $a->full_name;
//			$ale->amount = abs($Invoice->bill_amount);
//			$ale->link_to = ImperiumBase::getObjectType($Invoice);
//			$ale->link_id = $Invoice->id;

	// Debit Asset - Revenue
	$a = new Account($_ENV['account']['revenue_account_id']);
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = abs($Invoice['bill_amount']) * -1;
	// $ale['link_to'] = 'invoice'; // ImperiumBase::getObjectType($Invoice);
	// $ale['link_id'] = $Invoice['id'];
	$at->AccountLedgerEntryList[] = $ale;

	// Credit A/R - Sub Customer & Attach to Invoice
	$C = new Contact($Invoice['contact_id']);
	if (!empty($C['account_id'])) {
		$a = new Account($C['account_id']);
	} else {
		$a = new Account();
		// $a['id'] = 0;
		// $a['full_name'] = '- Unknown -';
	}
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = abs($Invoice['bill_amount']);
	$ale['link'] = sprintf('invoice:%d', $Invoice['id']); // ImperiumBase::getObjectType($Invoice);
	$at->AccountLedgerEntryList[] = $ale;

	// Debit Inbound Cash Asset (maybe Paypal if Payment Processor)
	//$a = new Account( $_ENV['account']['inbound_account_id'] );
	//$ale = new AccountLedgerEntry();
	//$ale['account_id'] = $a['id'];
	//$ale['account_name'] = $a['full_name'];
	//$ale['amount'] = abs($Invoice['bill_amount']) * -1;
	//$at->AccountLedgerEntryList[] = $ale;

	// Credit to Revenue Account
	//$a = new Account( $_ENV['account']['revenue_id'] );
	//$ale = new AccountLedgerEntry();
	//$ale['account_id'] = $a['id'];
	//$ale['account_name'] = $a['full_name'];
	//$ale['amount'] = abs($Invoice['bill_amount']);
	//$at->AccountLedgerEntryList[] = $ale;

	// Debit Sales Tax Account
	if (!empty($_ENV['account']['taxhold_account_id'])) {
		$a = new Account( $_ENV['account']['taxhold_account_id'] );
		$ale = new AccountLedgerEntry();
		$ale['account_id'] = $a['id'];
		$ale['account_name'] = $a['full_name'];
		$ale['amount'] = abs($Invoice['tax_total']);
		$ale['link_to'] = ImperiumBase::getObjectType($Invoice);
		$ale['link_id'] = $Invoice['id'];
	}
	// Credit Accounts Receivable

	$_SESSION['account-transaction'] = $at;
	$_SESSION['return-path'] = sprintf('/invoice/view?i=%d', $Invoice['id']);

	Radix::redirect('/account/transaction');

// Post Charges to Customer Account
case 'post':

	$C = new Contact($Invoice['contact_id']);
	if (empty($C['account_id'])) {
		Session::flash('fail', 'Cannot Post Invoice unless the Contact has an Account');
		Radix::redirect('/invoice/view?i=' . $Invoice['id']);
	}

	// Generate a Transaction to Post to This Clients Account Receivable

	$at = new \stdClass();
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry['date'] = $Invoice['date'];
	$at->AccountJournalEntry['note'] = 'Charge for Invoice #' . $Invoice['id'];
	$at->AccountLedgerEntryList = array();

	// Debit Accounts Receivable for this Client
	//$a = new Account($C['account_id']);
	//$ale = new AccountLedgerEntry();
	//$ale['account_id'] = $a['id'];
	//$ale['account_name'] = $a['full_name'];
	//$ale['amount'] = abs($Invoice['bill_amount']) * -1;
	//$at->AccountLedgerEntryList[] = $ale;

	// Debit Revenue
	$a = new Account( $_ENV['account']['revenue_id'] );
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = abs($Invoice['bill_amount']) * -1;
	// $ale['link_to'] = 'contact';
	// $ale['link_id'] = $Invoice['contact_id'];
	$at->AccountLedgerEntryList[] = $ale;

	// Credit Customer Account
	$a = new Account( $C['account_id'] );
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = abs($Invoice['bill_amount']);
	$ale['link_to'] = 'invoice';
	$ale['link_id'] = $Invoice['id'];
	$at->AccountLedgerEntryList[] = $ale;

	$_SESSION['account-transaction'] = $at;
	$_SESSION['return-path'] = sprintf('/invoice/view?i=%d', $Invoice['id']);

	Radix::redirect('/account/transaction');

	break;

// Save the Updated Invoice
case 'save':

	// Save Request
	foreach (array('contact_id','date','kind','status','bill_address_id','ship_address_id','note') as $x) {
		$Invoice[$x] = trim($_POST[$x]);
	}
	if (empty($Invoice['flag'])) {
		$Invoice->setFlag(Invoice::FLAG_OPEN);
	}
	$Invoice->save();

	if ($id) {
		Session::flash('info', 'Invoice #' . $Invoice['id'] . ' saved');
	} else {
		Session::flash('info', 'Invoice #' . $Invoice['id'] . ' created');
	}

	Radix::redirect('/invoice/view?i=' . $Invoice['id']);

	break;

// Email the Invoice
case 'send':

	$co = new Contact($Invoice['contact_id']);

	// Sent Good
	if ($_GET['sent']=='true') {
		$msg = 'Invoice #' . $Invoice['id'] . ' sent to ' . $this->_s->EmailSentMessage->to;
		unset($this->_s->EmailSentMessage->to);
		Base_Diff::note($iv,$msg);
		$this->_s->msg = $msg;
		$this->redirect('/invoice/view?i=' . $Invoice['id']);
	}

	$ah = Auth_Hash::make($Invoice);

	$this->_s->EmailComposeMessage = new \stdClass();
	$this->_s->EmailComposeMessage->to = $co['email'];
	//$ss->EmailComposeMessage->to = $co->email;
	//if ($co->kind != 'Person') {
	//	if ($list = $co->getContactList(Contact::FLAG_BILL)) {
	//		$to = array();
	//		foreach ($list as $x) {
	//			$to[] = $x->email;
	//		}
	//		$this->_s->EmailComposeMessage->to = implode(',',$to);
	//		$this->_s->EmailComposeMessage->body = 'Hello, ' . $list[0]->first_name . ",\n";
	//	}
	//}
	$this->_s->EmailComposeMessage->subject = 'Invoice #' . $Invoice['id'] . ' from ' . $_ENV['company']['name'];

	// Load Template File
	$file = APP_ROOT . '/approot/etc/invoice-mail.txt';
	if (is_file($file)) {

		$body = file_get_contents($file);

		// Substitutions
		$body = str_replace('$app_company',$_ENV['company']['name'],$body);
		$body = str_replace('$contact_name',$co['contact'],$body);
		$body = str_replace('$invoice_id', $Invoice['id'], $body);
		$body = str_replace('$invoice_date',strftime($_ENV['format']['nice_date'],strtotime($Invoice['date'])),$body);
		if (strpos($body,'$invoice_link')) {
			$ah = Auth_Hash::make($Invoice);
			$body = str_replace('$invoice_link',"{$_ENV['application']['base']}/hash/{$ah['hash']}",$body);
		}

		// @todo collect associated work-orders?
		$sql = 'SELECT DISTINCT workorder.id FROM workorder ';
		$sql.= ' JOIN workorder_item ON workorder.id = workorder_item.workorder_id ';
		$sql.= ' WHERE workorder_item.id IN ( ';
			$sql.= 'SELECT workorder_item_id FROM invoice_item WHERE invoice_item.invoice_id = %d ';
		$sql.= ') ';
		$sql.= ' ORDER BY 1 ';
		$res = $this->_d->fetchAll(sprintf($sql, $Invoice['id']));
		$id_list = array(); // IDs
		$al_list = array(); // Authorization Hash Links
		foreach ($res as $x) {
			$ah = Auth_Hash::make(new WorkOrder($x['id']));
			$id_list[] = sprintf('#%d',$x['id']);
			$al_list[] = sprintf('%s/hash/%s', $_ENV['application']['base'],$ah['hash']);
		}
		$body = str_replace('$workorder_id',implode(', ',$id_list),$body);
		$sep = "\n  "; // Default, use matched if one is found
		if (preg_match('/^(.+)\$workorder_link/m',$body,$m)) {
			$sep = "\n{$m[1]}";
		}
		$body = str_replace('$workorder_link',implode($sep,$al_list),$body);

		$body = str_replace('$payment_link',"{$_ENV['application']['base']}/checkout/invoice/hash/{$Invoice->hash}",$body);

		$this->_s->EmailComposeMessage->body = $body;

	}

	$this->_s->ReturnGood = sprintf('/invoice/view?i=%d&sent=good',$Invoice['id']);
	$this->_s->ReturnFail = sprintf('/invoice/view?i=%d&sent=fail',$Invoice['id']);

	Radix::redirect('/email/compose');
	break;

case 'void':

	// Voiding out an Invoice
	$Invoice['note'] = trim($_POST['note']);
	$Invoice['status'] = 'Void';
	$Invoice['bill_amount'] = 0;
	$Invoice['paid_amount'] = 0;
	$Invoice->setFlag(Invoice::FLAG_VOID);
	$Invoice->save();

	Radix::redirect('/');

	break;
}
