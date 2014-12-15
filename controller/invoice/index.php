<?php
/**
	InvoiceController indexAction
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

if (empty($_GET['size'])) $_GET['size'] = 50;

// Get Counts
$this->page_max = SQL::fetch_one('SELECT count(id) FROM invoice');
$this->page_cur = min(max(1, $_GET['page']), $this->page_max);
$_GET['size'] = min(max(20, $_GET['size']), 100);

$sql = 'SELECT invoice.*, contact.name as contact_name ';
$sql.= ' FROM invoice ';
$sql.= ' JOIN contact ON invoice.contact_id=contact.id ';
$sql.= ' ORDER BY invoice.date DESC, invoice.id DESC ';
$sql.= ' OFFSET ' . (($this->page_cur-1) * $_GET['size']);
$sql.= ' LIMIT ' . $_GET['size'];

$this->list = SQL::fetch_all($sql);

$a_id = $this->list[0]['id'];
$z_id = $this->list[ count($this->list) - 1]['id'];

$title = array();
$title[] = sprintf('Invoices %d through %d',$a_id,$z_id);
$title[] = sprintf('Page %d of %d', $this->page_cur, ceil($this->page_max / $_GET['size']));
$_ENV['title'] = $title;
