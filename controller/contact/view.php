<?php
/**

*/

$c = new Contact(intval($_GET['c']));
if (!$c->id) {
	radix_session::flash('fail', 'Contact not found');
	radix::redirect('/contact');
}

$this->Contact = $c;
$this->ContactList = radix_db_sql::fetch_all("select * from contact where id != ? AND (parent_id = ? OR company = ?)",array($c->id,$c->id,$c->company));
$this->ContactAddressList = $c->getAddressList();
$this->ContactChannelList = $c->getChannelList();
$this->ContactNoteList = $c->getNotes();
$this->ContactFileList = $c->getFiles();
// @note what does order by star, status do? Join base_enum?
$this->WorkOrderList = radix_db_sql::fetchAll("select * from workorder where contact_id={$c->id} order by date desc, id desc");
$this->InvoiceList = radix_db_sql::fetchAll("select * from invoice where contact_id={$c->id} order by date desc, id desc");

// Why Pointing this way?
$this->Account = $c->getAccount();

// $this->_s->Contact = $c;
$_SESSION['Contact'] = $c;

$_ENV['title'] = array(
	$this->Contact->kind,
	$this->Contact->name
);
