<?php
/**
 * Render the Invoice as a PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;

Radix::$theme_name = 'pdf';

$iv = [];
if (!empty($_GET['i'])) {
	$iv = new Invoice($_GET['i']);
}

if (empty($iv['id'])) {
	throw new Exception('Invalid Invoice Id',__LINE__);
}

$pdf = new PDF\Invoice();
$pdf->loadInvoice($iv);

$pdf->output(sprintf('Invoice-%d.pdf', $iv['id']), 'I');

exit(0);
