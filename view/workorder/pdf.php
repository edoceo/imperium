<?php
/**
 * Render the WorkOrder as a PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;

Radix::$theme_name = 'pdf';

$obj = [];
if ( ! empty($_GET['id'])) {
	$obj = new WorkOrder($_GET['id']);
} elseif ( ! empty($_GET['w'])) {
	$obj = new WorkOrder($_GET['w']);
}

if (empty($obj['id'])) {
	throw new \Exception('Invalid WorkOrder [VWP-022]');
}

$pdf = new PDF\WorkOrder();
$pdf->setData($obj);
$pdf->output(sprintf('WorkOrder-%d.pdf', $obj['id']), 'I');

exit(0);

// LEGACY

// $pdf = new WorkOrderPDF($wo);

// $this->view->file = new stdClass();
// $this->view->file->name = 'WorkOrder-' . $wo->id . '.pdf';
// $this->view->file->data = $pdf->render();
// $this->view->file->size = strlen($this->view->file->data);

// $this->_helper->viewRenderer->setNoRender(); //supress auto renderning
