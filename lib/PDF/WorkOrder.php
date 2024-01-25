<?php
/**
 * WorkOrder as PDF
 *
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * @copyright    2008 Edoceo, Inc
 * @package    edoceo-imperium
 * @link       http://imperium.edoceo.com
 * @since      File available since Release 1013
 */

namespace Edoceo\Imperium\PDF;

use Edoceo\Imperium\Contact;
use Edoceo\Imperium\ContactAddress;

class WorkOrder extends Base
{
	private $_page_c = 1;

	function setData($wo)
	{
		$this->setTime(strtotime($wo['date']));
		$this->setTitle(sprintf('WorkOrder #%d', $wo['id']));

		$this->addPage();

		$this->drawData($wo);
	}


	function drawData($wo)
	{
		$co = new Contact($wo['contact_id']);

		$this->drawData_header($wo, $co);

		$this->drawData_line_items($wo);

	}

	function drawData_header($wo, $co)
	{
		// Bill Address
		$this->setFont('', '', 12);
		$this->setXY(0.5, 2.25);
		$this->cell(0.75, 3/16, 'Client:');

		// Data
		$this->setFont('', 'B', 12);
		$this->setXY(1.25, 2.25);
		$this->cell(3, 3/16, $co['company']);

		$this->setFont('', '', 12);
		$this->setXY(0.5, 3);
		$this->cell(0.75, 3/16, 'Notes:');

		$this->setFont('', 'B', 12);
		$this->setXY(1.25, 3);
		$this->cell(1, 3/16, $iv['note']);

		$this->setXY(0.5, 3.25);

		// Base Rate
		// $y -= 14;
		// $page->setFont($font_hb,12);
		// $page->drawText('Base Rate: ',36, $y);
		// $page->setFont($font_h,12);
		// $page->drawText($wo->base_rate . '/' . $wo->base_unit, 108, $y);

		// LEGACY

		// $y -= 14;
		// $page->drawText('Client: ' . $co->name, 36, $y);
		// $page->drawText('Phone: ' . $co->phone, 306, $y);

		// // Requester & Email
		// $y -= 14;
		// $page->drawText('Requester: ' . $wo->requester, 36, $y);
		// $page->drawText('Email: ' . $co->email, 306, $y);

		// // Summary
		// $y -= 14;
		// $page->drawText('Note: ', 36, $y);
		// if (strlen($wo->note)) {
		// 	$y += 12; // Rewind
		// 	$page->setFont($font_h,12);
		// 	$lines = explode("\n",wordwrap(stripslashes($wo->note),80));
		// 	foreach ($lines as $line) {
		// 		$y -= 12;
		// 		$page->drawText($line,72,$y);
		// 	}
		// }


	}

	function drawData_line_items($wo)
	{
		$woi_list = $wo->getWorkOrderItems();

		$this->drawData_line_item_column_header();
		$y = $this->getY();

		// Items Table
		$a_cost = $a_cost_full = $e_cost = $e_cost_full = 0;
		$a_size = $a_size_full = $e_size = $e_size_full = 0;
		$woi_summary_list = array();

		foreach ($woi_list as $woi) {

			$name = strtotime($woi['date']) > 0 ? date('m/d',strtotime($woi['date'])) . ' ' : '';
			$name.= $woi['kind'] . ': ';
			$name.= $woi['name'];

			$this->setFont('', 'B', 12);
			$this->setXY(0.5, $y);
			$this->cell(4.5, self::LH_12, $name);

			// Quantity Information
			$txt = '';
			// if (!empty($woi['a_tax_rate']) || !empty($woi->e_tax_rate)) $t = 't';
			// if (floatval($woi['a_quantity']) <= 0) {
				// $page->setFont($font_h,12);
				// $txt .= sprintf('%.3f %s', $woi->e_quantity,$woi->e_unit);
				// $e_cost += ($woi->e_quantity * $woi->e_rate);
			// } else {
				$txt .= sprintf('%.2f %s', $woi['a_quantity'], $woi['a_unit']);
				// $a_cost += ($woi->a_quantity * $woi->a_rate);
			// }
			$this->setXY(7.0, $y);
			$this->cell(1, self::LH_12, $txt, 0, 0, 'R');

			$y += self::LH_14;

			// Line Item Detail
			if (strlen($woi['note'])) {

				$this->setFont('', '', 12);
				$this->setXY(0.75, $y);
				$this->multiCell(7, self::LH_12, $woi['note'], 0, 'L', 0, 2);

			}

			$y = $this->getY();

			if ($y >= 9.5) {
				$this->addPage();
				$this->setXY(0.5, 2.25);
				$this->drawData_line_item_column_header();
				$y = $this->getY();
			}

		}

	}

	/**
	 *
	 */
	function drawData_line_item_column_header()
	{
		$y = $this->getY();

		$this->setFont('', 'B', 14);

		// Note
		$this->setXY(0.5, $y);
		$this->cell(4.5, self::LH_14, 'Item');

		// Quantity
		$this->setXY(7.0, $y);
		$this->cell(1, self::LH_14, 'Quantity', 0, 0, 'R');

		$y += self::LH_16;

		// Blue Line Below
		$this->setDrawColor(0x33, 0x66, 0x99);
		$this->setLineWidth(1/32);
		$this->line(0.5, $y, 8, $y);

		$this->setXY(7.0, $y);

	}

}
