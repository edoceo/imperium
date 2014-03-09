<?php
/**
	InvoiceController itemAction

	View/Edit an Item
*/

$ii = new InvoiceItem(intval($_GET['id']));

switch (strtolower($_POST['a'])) {
case 'cancel':
	radix::redirect('/invoice/view?i=' . $ii['invoice_id']);
	break;
case 'delete':
	$ii->delete();
	radix_session::flash('info', sprintf('Invoice Item #%d was deleted',$ii['id']));
	radix::redirect('/invoice/view?i=' . $ii['invoice_id']);
	break;
case 'save':

	$ii['invoice_id'] = intval($_POST['invoice_id']);
	foreach (array('kind','date','quantity','rate','unit','name','note','tax_rate') as $x) {
		$ii[$x] = trim($_POST[$x]);
	}
	// Save to DB
	$ii->save();
	radix_session::flash('info', sprintf('Invoice Item #%d saved',$ii['id']));
	// @todo Update the Balance (Sloppy, should be in IV->saveItem()
	$iv = new Invoice($_POST['invoice_id']);
	$iv->save();

	radix::redirect('/invoice/view?i=' . $ii['invoice_id']);
	break;
// case 'create':
default: // Create

	// Create
	if ( (empty($_GET['id'])) && (!empty($_GET['i'])) && (intval($_GET['i'])>0) ) {
		$this->title = array('Invoice','Item','Create');
		$this->Invoice = new Invoice(intval($_GET['i']));
		$this->InvoiceItem = new InvoiceItem(null);
		$this->InvoiceItem['invoice_id'] = $this->Invoice['id'];
		return(0);
	}

	// View
	$this->InvoiceItem = new InvoiceItem(intval($_GET['id'])); // $db->fetchRow("select * from invoice_item where id = $id");
	$this->Invoice = new Invoice($this->InvoiceItem->invoice_id); // $db->fetchRow("select * from invoice where id = {$this->view->InvoiceItem->invoice_id}");

	$_ENV['title'] = array('Invoice','#'.$this->Invoice->id,'Item','#' . $this->InvoiceItem['id']);
}
