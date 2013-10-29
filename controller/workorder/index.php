<?php
/**
	WorkorderController indexAction
*/

// $sql = $this->_d->select();
// $sql->from('workorder');
// $sql->join('contact','workorder.contact_id=contact.id',array('contact.name as contact_name'));
// $sql->order(array('workorder.date desc','workorder.id desc'));

$sql = 'SELECT workorder.*, contact.name as contact_name ';
$sql.= ' FROM workorder ';
$sql.= ' JOIN contact ON workorder.contact_id=contact.id ';
$sql.= ' ORDER BY workorder.date DESC, workorder.id DESC ';
$sql.= ' LIMIT 250 '; 
// $page = Zend_Paginator::factory($sql);
// if (count($page)==0) {
// 	$this->view->title = 'No Work Orders';
// 	return(0);
// }

// $page->setCurrentPageNumber(intval($_GET['page']));
// $page->setItemCountPerPage(30);
// $page->setPageRange(10);

// $a_id = $page->getItem(1)->id;
// $z_id = $page->getItem($page->getCurrentItemCount())->id;

$title = array();
// $title[] = sprintf('Work Orders %d through %d',$a_id,$z_id);
// $title[] = sprintf('Page %d of %d',$page->getCurrentPageNumber(),$page->count());
$_ENV['title'] = $title;

$this->list = radix_db_sql::fetch_all($sql);
