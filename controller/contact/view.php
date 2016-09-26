<?php
/**

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

if (empty($_GET['c'])) {
	return(0);
}

$c = new Contact(intval($_GET['c']));
if (empty($c['id'])) {
	Session::flash('fail', 'Contact not found');
	// Radix::redirect('/contact');
}
$_ENV['contact'] = $c;

$this->Contact = $c;
// Why Pointing this way?
$this->Account = $c->getAccount();

$this->ContactList = array();

if (empty($c['parent_id'])) {
	// $this->ContactList = SQL::fetch_all("SELECT * FROM contact WHERE id != ? AND (parent_id = ? OR company = ?)",array($c->id,$c->id,$c->company));
	$sql = 'SELECT * FROM contact WHERE id != ? AND parent_id = ?';
	$arg = array($c['id'], $c['id']);
	$this->ContactList = SQL::fetch_all($sql, $arg);
}
$this->ContactAddressList = $c->getAddressList();
$this->ContactChannelList = $c->getChannelList();
$this->ContactNoteList = $c->getNotes();
$this->ContactFileList = $c->getFiles();
// @note what does order by star, status do? Join base_enum?
$this->WorkOrderList = SQL::fetch_all('SELECT workorder.*, contact.name AS contact_name FROM workorder JOIN contact ON workorder.contact_id = contact.id WHERE workorder.contact_id = ? ORDER BY workorder.date DESC, workorder.id DESC', array($c['id']));
$this->InvoiceList = SQL::fetch_all('SELECT * FROM invoice WHERE contact_id = ? ORDER BY date DESC, id DESC', array($c['id']));

$_ENV['title'] = array(
	$this->Contact['kind'],
	sprintf('#%d:%s', $this->Contact['id'], $this->Contact['name'])
);
