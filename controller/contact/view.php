<?php
/**

*/

$c = new Contact(intval($_GET['c']));
if (empty($c['id'])) {
	radix_session::flash('fail', 'Contact not found');
	// radix::redirect('/contact');
}
$_ENV['contact'] = $c;

$this->Contact = $c;
$this->ContactList = array();

if (empty($c->parent_id)) {
	// $this->ContactList = radix_db_sql::fetch_all("SELECT * FROM contact WHERE id != ? AND (parent_id = ? OR company = ?)",array($c->id,$c->id,$c->company));
	$this->ContactList = radix_db_sql::fetch_all("SELECT * FROM contact WHERE id != ? AND parent_id = ?",array($c->id,$c->id));
}
$this->ContactAddressList = $c->getAddressList();
$this->ContactChannelList = $c->getChannelList();
$this->ContactNoteList = $c->getNotes();
$this->ContactFileList = $c->getFiles();
// @note what does order by star, status do? Join base_enum?
$this->WorkOrderList = radix_db_sql::fetchAll("SELECT workorder.*, contact.name as contact_name FROM workorder JOIN contact ON workorder.contact_id = contact.id WHERE workorder.contact_id={$c['id']} ORDER BY workorder.date DESC, workorder.id DESC");
$this->InvoiceList = radix_db_sql::fetchAll("select * from invoice where contact_id={$c->id} order by date desc, id desc");

// Why Pointing this way?
$this->Account = $c->getAccount();

$_ENV['title'] = array(
	$this->Contact['kind'],
	sprintf('#%d:%s', $this->Contact['id'], $this->Contact['name'])
);
