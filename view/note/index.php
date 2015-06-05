<?php
/**
    @file
    @brief List of Notes
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

$sql = 'SELECT id, name, cts FROM base_note'; // $this->_d->select();
$sql.= ' WHERE link IS NULL OR link = \'\'';
$sql.= ' ORDER BY name';
// $sql->from('base_note',array('id','name','cts'));
// $sql->where('link is null');
// $sql->order(array('name'));

$res = SQL::fetch_all($sql);

// $page = Zend_Paginator::factory($sql);
// $page->setCurrentPageNumber($this->_request->getParam('page'));
// $page->setItemCountPerPage(30);
// $page->setPageRange(10);

// if ($cpage->getTotalItemCount()) {
if (0 != count($res)) {
	$a_id = $res[0]['id'];
	$z_id = $res[count($res)]['id'];

	$title = array();
	$title[] = sprintf('Notes %d through %d',$a_id,$z_id);
	$title[] = sprintf('Page %d of %d', 1, 99);
	$_ENV['title'] = $title;
} else {
	$_ENV['title'] = 'Notes';
	Session::flash('info', 'There are no notes created');
}

// $this->view->Page = $page;

// echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');

echo Radix::block('note-list',array(
	'list' => $res,
	'page' => 1,
));

// echo $this->paginationControl($this->Page,'All','../elements/page-control.phtml');
