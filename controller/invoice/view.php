<?php
/**
	Invoice View Action
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

$id = intval($_GET['i']);

$this->Invoice = new Invoice($id);
if ( (!empty($_GET['sent'])) && ($_GET['sent'] == 'good') ) {
	Radix\Session::flash('info', 'Invoice Status updated');
	$this->Invoice['status'] = 'Sent';
	$this->Invoice->setFlag(Invoice::FLAG_SENT);
	$this->Invoice->save();
	// Base_Diff::note($this->Invoice, $this->_s->info);
}
$_ENV['invoice']['id'] = $this->Invoice['id'];

$this->Contact = new Contact($this->Invoice['contact_id']);
$this->ContactAddressList = SQL::fetch_mix('select id,address from contact_address where contact_id = ?', array($this->Invoice['contact_id']));
$this->InvoiceItemList = $this->Invoice->getInvoiceItems();
$this->InvoiceNoteList = $this->Invoice->getNotes();
$this->InvoiceFileList = $this->Invoice->getFiles();
$this->InvoiceHistoryList = $this->Invoice->getHistory();
$this->InvoiceTransactionList = $this->Invoice->getTransactions();

// Add Prev / Next Links
$this->jump_list = array();
if ( ! empty($this->Invoice['id'])) {

	// Previous Ones
	$s = sprintf('SELECT id FROM invoice where id < %d order by id desc limit 5',$this->Invoice['id']);
	$r = SQL::fetch_all($s);
	$r = array_reverse($r);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x['id']);
	}

	// This One
	$this->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$this->Invoice['id']);

	// Next Ones
	$s = sprintf('SELECT id FROM invoice where id > %d order by id asc limit 5',$this->Invoice['id']);
	$r = SQL::fetch_all($s);
	foreach ($r as $x) {
		$this->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x['id']);
	}
}
