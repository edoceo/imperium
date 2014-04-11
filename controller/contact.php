<?php
/**

*/

$this->KindList       = radix_db_sql::fetch_mix($sql, array('contact-kind'));
$this->StatusList     = radix_db_sql::fetch_mix($sql, array('contact-status'));

function createAction()
{
	$this->view->title = array('Contact','Create');
	$this->view->Contact = new Contact(null);
	$P = new Contact(intval($_GET['parent']));
	if ($P->id) {
		$this->view->Contact->parent_id = $P->id;
		$this->view->Contact->company = $P->company;
		$this->view->Contact->phone = $P->phone;
		$this->view->Contact->url = $P->url;
	}
	$this->render('view');
}
