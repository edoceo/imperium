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
	private $_pdf;

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

		//$font = Zend_Pdf_Font::fontWithPath(APP_ROOT . '/var/fonts/Edoceo-MICR.ttf');
		// $font_h = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		// $font_hb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

		$this->drawData_line_item_column_header();

		$y = $this->getY();
		$y = $y + (self::LH_12 * 2);

		// Items Table
		$a_cost = $a_cost_full = $e_cost = $e_cost_full = 0;
		$a_size = $a_size_full = $e_size = $e_size_full = 0;
		$woi_summary_list = array();

		foreach ($woi_list as $woi) {

			if (empty($woi_summary_list[$woi['status']])) $woi_summary_list[$woi['status']] = array();
			if (empty($woi_summary_list[$woi['status']][$woi['kind']])) $woi_summary_list[$woi['status']][$woi['kind']] = 0;

			$name = strtotime($woi['date']) > 0 ? date('m/d',strtotime($woi['date'])) . ' ' : null;
			$name.= $woi['kind'] . ': ';
			$name.= $woi['name'];

			$this->setFont('', 'B', 12);
			// $page->drawText($name,36,$y);
			$this->setXY(0.5, $y);
			$this->cell(4.5, self::LH_12, $name);

			$y += self::LH_14;

			// Line Item Detail
			if (strlen($woi['note'])) {

				$this->setFont('', '', 12);
				$this->setXY(0.75, $y);
				$this->multiCell(7, self::LH_12, $woi['note'], 0, 'L', 0, 2);
				$y = $this->getY();

				if ($y >= 10) {
					$this->addPage();
					$y = 2.5;
				}

			}

		}

	}

	function drawData_line_item_column_header()
	{
		// $this->setFont(null, 'B', 14);
		// Note
		$this->setXY(0.5, 3.5);
		$this->cell(4.5, 4/16, 'Item');

		// Quantity
		$this->setXY(5.0, 3.5);
		$this->cell(1, 4/16, 'Quantity');

		// Price
		$this->setXY(6.0, 3.5);
		$this->cell(1, 4/16, 'Price');

		// Total
		$this->setXY(7.0, 3.5);
		$this->cell(1, 4/16, 'Cost');

	}

	function x()
	{

		// Column Headers
		// $y -= 16;
		// $page->setFont($font_hb,16);
		// $page->setLineWidth(1);
		// $page->drawText('Item', 36, $y);
		// $page->drawText('Quantity', 504, $y);
		// $y -= 4;
		// $page->drawLine(36,$y,576,$y);
		// $y -= 16;

		// Items Table
		$a_cost = $a_cost_full = $e_cost = $e_cost_full = 0;
		$a_size = $a_size_full = $e_size = $e_size_full = 0;
		$woi_summary_list = array();

		foreach ($woi_list as $woi) {

			if (empty($woi_summary_list[$woi->status])) $woi_summary_list[$woi->status] = array();
			if (empty($woi_summary_list[$woi->status][$woi->kind])) $woi_summary_list[$woi->status][$woi->kind] = 0;

			// Cost
			$t = null;
			if (!empty($woi->a_tax_rate) || !empty($woi->e_tax_rate)) $t = 't';
			if (floatval($woi->a_quantity) <= 0) {
				$page->setFont($font_h,12);
				$t.= sprintf('%.3f %s',$woi->e_quantity,$woi->e_unit);
				$e_cost += ($woi->e_quantity * $woi->e_rate);
				$woi_summary_list[$woi->status][$woi->kind] += floatval($woi->e_quantity * $woi->e_rate);
			} else {
				$t.= sprintf('%.3f %s',$woi->a_quantity,$woi->a_unit);
				$a_cost += ($woi->a_quantity * $woi->a_rate);
				$woi_summary_list[$woi->status][$woi->kind] += floatval($woi->a_quantity * $woi->a_rate);
			}
			$w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
			$page->drawText($t,576 - $w,$y);
			$y -= 12;

			// Note
			if (strlen($woi->note)) {
				//$page->setFont($font_hb,12);
				//$page->drawText('Resolution:',32,$y);
				$page->setFont($font_h,12);
				$lines = explode("\n",wordwrap(stripslashes($woi->note),90));
				foreach ($lines as $line) {
					$page->drawText($line,54,$y);
					$y -= 12;
				}
				$y -= 12;
			}

			if ($y <= 72) {
				$pdf->pages[] = $page;
				$y = 760;
				$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
			}
		}

		// @todo try to determine how many lines we need, then make sure we have that many?
		$need = 12;
		foreach ($woi_summary_list as $stat) {
			$need += 14;
			foreach ($stat as $x) {
				$need += 14;
			}
		}
		$need += 14;

		if ($y <= $need) {
			$pdf->pages[] = $page;
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
			$y = 630;
		}
		if ($y >= 644) {
			$y = 644;
		}

		// Charge Summary
		$y -= 12;
		$page->setFont($font_hb,14);
		$page->drawText('Charge Summary',36,$y);
		$page->setLineColor( new Zend_Pdf_Color_Html('#1a6293') );
		$page->setLineWidth(1);
		// $page->drawLine(36,$y-2,576,$y-2);

		// Kind Summary Line?
		$full_cost = 0;

		foreach ($woi_summary_list as $stat=>$woi_sub_list) {

			$y -= 14;
			$page->setFont($font_hb,12);
			$page->drawText("$stat:",36,$y);
			$stat_cost = 0;
			$stat_line = $y;

			$page->setFont($font_h,12);
			foreach ($woi_sub_list as $kind=>$cost) {
				$y -= 14;
				// Item Type
				$page->drawText($kind,72,$y);
				// Cost
				$t = '$' . sprintf('%.2f',$cost);
				$w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
				$page->drawText($t,576 - $w - 72,$y);
				//$page->drawText($v,504,$y);
				$stat_cost += $cost;
				$full_cost += $cost;
			}
			$page->drawLine(36,$y-2,576,$y-2);

			// Update Summary Line
			$page->setFont($font_hb,12);
			$t = '$' . sprintf('%.2f',$stat_cost);
			$w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
			$page->drawText($t,576 - $w,$stat_line);
		}
//
		// Total Toal
//        $page->setLineColor( new Zend_Pdf_Color_Html('#1a6293') );
//        $page->drawLine(36,$y+10,576,$y+10);
		$y -= 14;
		$page->setFont($font_hb,14);
		$page->drawText('Total:',36,$y);
//        $page->drawText('Total:',72,$y);
		$t = '$' . sprintf('%.2f',$full_cost);
		$w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
		$page->drawText($t,576 - $w,$y);

		$pdf->pages[] = $page;
		$this->_pdf = $pdf;
	}

}
