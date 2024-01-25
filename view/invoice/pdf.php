<?php
/**
 * Render the Invoice as a PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;

Radix::$theme_name = 'pdf';

$obj = [];
if ( ! empty($_GET['id'])) {
	$obj = new Invoice($_GET['id']);
} elseif ( ! empty($_GET['i'])) {
	$obj = new Invoice($_GET['i']);
}

if (empty($obj['id'])) {
	throw new \Exception('Invalid Invoice [VIP-022]');
}

$pdf = new PDF\Invoice();
$pdf->setData($obj);
$pdf->output(sprintf('Invoice-%d.pdf', $obj['id']), 'I');

exit(0);
