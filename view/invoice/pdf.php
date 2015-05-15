<?php
/**
	Render the Invoice as a PDF
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;

Radix::$theme_name = 'pdf';

// $l = Zend_Layout::getMvcInstance();
// $l->setLayout('pdf');

$iv = null;
if (!empty($_GET['i'])) {
	$iv = new Invoice($_GET['i']);
} else {
	$ss = Zend_Registry::get('session');
	if (isset($ss->Invoice)) {
		$iv = $ss->Invoice;
	} else {
		$iv = new Invoice($_GET['i']);
	}
}
if (empty($iv['id'])) {
	throw new Exception('Invalid Invoice Id',__LINE__);
}

$pdf = new PDF\Invoice();
$pdf->loadInvoice($iv);

// $this->view->file = new stdClass();
// $this->view->file->name = 'Invoice-' . $iv->id . '.pdf';
// $this->view->file->data = $pdf->render();
// $this->view->file->size = strlen($this->view->file->data);

// $this->_helper->viewRenderer->setNoRender();//supress auto renderning

$pdf->output(sprintf('Invoice-%d.pdf', $iv['id']), 'I');

exit(0);