<?php
/**
	Invoice View Action
*/

$id = intval($_GET['i']);
$this->Invoice = new Invoice($id);
if ( (!empty($_GET['sent'])) && ($_GET['sent'] == 'good') ) {
	$this->_s->info[] = 'Invoice Status updated';
	$this->Invoice->status = 'Sent';
	$this->Invoice->setFlag(Invoice::FLAG_SENT);
	$this->Invoice->save();
	Base_Diff::note($this->Invoice,$this->_s->info);
}
$this->Contact = new Contact($this->Invoice->contact_id);
$this->ContactAddressList = radix_db_sql::fetchMix("select id,address from contact_address where contact_id={$this->Invoice->contact_id}");
$this->InvoiceItemList = $this->Invoice->getInvoiceItems();
$this->InvoiceNoteList = $this->Invoice->getNotes();
$this->InvoiceFileList = $this->Invoice->getFiles();
$this->InvoiceHistoryList = $this->Invoice->getHistory();
$this->InvoiceTransactionList = $this->Invoice->getTransactions();

// Add Prev / Next Links
$this->jump_list = array();
if (!empty($this->Invoice->id)) {
	$s = sprintf('SELECT id FROM invoice where id < %d order by id desc limit 5',$this->Invoice->id);
	$r = radix_db_sql::fetchAll($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x->id);
	}
	$s = sprintf('SELECT id FROM invoice where id > %d order by id asc limit 5',$this->Invoice->id);
	$r = radix_db_sql::fetchAll($s);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x->id);
	}
}

// $this->_s->Invoice = $this->view->Invoice;
$_SESSION['invoice'] = $this->Invoice;