<?php
/**
	WorkorderController indexAction
*/

if (empty($_GET['size'])) $_GET['size'] = 50;

// Get Counts
$this->page_max = radix_db_sql::fetch_one('SELECT count(id) FROM workorder');
$this->page_cur = min(max(1, $_GET['page']), $this->page_max);
$_GET['size'] = min(max(20, $_GET['size']), 100);

$sql = 'SELECT workorder.*, contact.name as contact_name ';
$sql.= ' FROM workorder ';
$sql.= ' JOIN contact ON workorder.contact_id=contact.id ';
$sql.= ' ORDER BY workorder.date DESC, workorder.id DESC ';
$sql.= ' OFFSET ' . (($this->page_cur-1) * $_GET['size']);
$sql.= ' LIMIT ' . $_GET['size'];

$this->list = radix_db_sql::fetch_all($sql);

$a_id = $this->list[0]['id'];
$z_id = $this->list[ count($this->list) - 1]['id'];

$title = array();
$title[] = sprintf('Work Orders %d through %d',$a_id,$z_id);
$title[] = sprintf('Page %d of %d', $this->page_cur, ceil($this->page_max / $_GET['size']));
$_ENV['title'] = $title;
