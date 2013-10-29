<?php
/**
	InvoiceController indexAction
*/

// $sql = $this->_d->select();
// $sql->from('invoice');
// $sql->join('contact','invoice.contact_id=contact.id',array('contact.name as contact_name'));
// $sql->order(array('invoice.date desc','invoice.id desc'));

$sql = 'SELECT invoice.*, contact.name as contact_name ';
$sql.= ' FROM invoice ';
$sql.= ' JOIN contact ON invoice.contact_id=contact.id ';
$sql.= ' ORDER BY invoice.date DESC, invoice.id DESC ';
$sql.= ' LIMIT 250 '; 
// $page = Zend_Paginator::factory($sql);
// if (count($page)==0) {
// 	$this->view->title = 'No Invoices';
// 	return(0);
// }

// $page->setCurrentPageNumber(intval($_GET['page']));
// $page->setItemCountPerPage(50);
// $page->setPageRange(10);

// $a_id = $page->getItem(1)->id;
// $z_id = $page->getItem($page->getCurrentItemCount())->id;

$title = array();
// $title[] = sprintf('Invoices %d through %d',$a_id,$z_id);
// $title[] = sprintf('Page %d of %d',$page->getCurrentPageNumber(),$page->count());
$_ENV['title'] = $title;

$this->list = radix_db_sql::fetchAll($sql);

