<?php
/**
	Get as PDF

	@copyright  2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
*/

namespace Edoceo\Imperium\PDF;

use Edoceo\Imperium\Contact;
use Edoceo\Imperium\ContactAddress;

class Invoice extends Base
{
	/**
		Load the Invoice to this PDF
	*/
	function loadInvoice($iv)
	{
		$x = 0;
		$y = 0;

		$this->setTime(strtotime($iv['date']));
		$this->setTitle(sprintf('Invoice #%d', $iv['id']));

		$co = new Contact($iv['contact_id']);
		$ba = new ContactAddress($iv['bill_address_id']);
		$sa = new ContactAddress($iv['ship_address_id']);

		//$font = Zend_Pdf_Font::fontWithPath(APP_ROOT . '/var/fonts/Edoceo-MICR.ttf');
		//$font_h = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		//$font_hb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

		$this->addPage();
		$this->MyHeader();

		// Bill Address
		$this->setFont('', '', 12);
		$this->setXY(0.5, 2.5);
		$this->cell(0.75, 3/16, 'Bill To:');

		$this->setXY(4.25, 2.5);
		$this->cell(0.75, 3/16, 'Ship To:');

		$this->setXY(0.5, 3);
		$this->cell(0.75, 3/16, 'Notes:');

		// Data
		$this->setFont('', 'B', 12);
		$this->setXY(1.25, 2.5);
		$this->cell(3, 3/16, $co['company']);

		$this->setXY(5.00, 2.5);
		$this->cell(3, 3/16, $co['company']);

		// @todo Somethign about New-Lines
		$this->setXY(1.25, 3);
		$this->cell(1, 3/16, $iv['note']);

		//$a = explode("\n",$ba->address);
		//foreach ($a as $t) {
		//	$this->drawText($t, 74, $y);
		//	$y-=12;
		//}

		// Ship Address
		//if (strlen($sa->address)) {
		//	$y = 612;
		//	$this->drawText($co->name, 352, $y);
		//	$y -= 12;
		//	$this->drawText($sa->address, 352, $y);
		//}

		//$y -= 12;
		//$this->setFontSize(12);

		// Column Headers
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

		// Blue Line Below
		$this->setDrawColor(0x33, 0x66, 0x99);
		$this->setLineWidth(1/32);
		$this->line(0.5, 3.75, 8, 3.75);

		// Column LInes
		$this->setLineWidth(1/64);
		$this->line(5.00, 3.75, 5.00, 9.75);
		$this->line(6.00, 3.75, 6.00, 9.75);
		$this->line(7.00, 3.75, 7.00, 9.75);
		//$this->line(576,$y,576,94);

		// Items Table
		$this->setFont('', '', 12);
		$y = 3.75 + ( 1 / 16 );
		$sub_total = 0;
		$tax_total = 0;
		$ivi_list = $iv->getInvoiceItems();
		foreach ($ivi_list as $ivi) {

			// $this->setFont(null, '', 12);

			// Multi Line Name
			$this->setXY(0.5, $y);
			$this->cell(4.5, 3/16, substr(trim($ivi['name']),0,55));
			/*
			$lines = explode("\n",wordwrap(stripslashes($ivi->name),55));
			foreach ($lines as $line) {
				$dy -= 12;
				$page->drawText($dy . '-'. $line,36,$dy);
			}
			*/

			//$page->drawText($ivi->note,32,$y);
			$this->setXY(5.0, $y);
			$this->cell(1, 3/16, sprintf('%.2f',$ivi['quantity']), null, null, 'R');

			$t = sprintf('%.2f',$ivi['rate']) .  '/' . $ivi['unit'];
			$this->setXY(6.0, $y);
			$this->cell(1, 3/16, $t, null, null, 'R');

			$t = sprintf('%.2f', $ivi['quantity'] * $ivi['rate']);
			$this->setXY(7.0, $y);
			$this->cell(1, 3/16, $t, null, null, 'R');

			$y += (4/16);

			//if ($y <= 108) {
			//	$pdf->pages[] = $page;
			//	$y = 760;
			//	$page = $this->addPage();
			//}

			// Add Sums
			$sub_total += ($ivi['quantity'] * $ivi['rate']);
			$tax_total += ( ($ivi['quantity'] * $ivi['rate']) * floatval($ivi['tax_rate']) );
		}

		// Single Line
		$y += 1/16;
		$this->line(0.5, $y, 8, $y);
		$y += 1/16;

		// Sub Total Line
		$this->setFont('', 'B', 12);
		$this->setXY(0.5, $y);
		$this->cell(4.5, 4/16, 'Sub Total:');

		$this->setXY(7, $y);
		$this->cell(1, 4/16, sprintf('$%.2f', $sub_total), null, null, 'R');
		$y += 4/16;

		// Tax Total Line
		$this->setXY(0.5, $y);
		$this->cell(4.5, 4/16, 'Sales Tax:');

		$this->setXY(7, $y);
		$this->cell(1, 4/16, sprintf('$%.2f', $tax_total), null, null, 'R');

		$y += 4/16;

		// Double Line
		$y += 1/16;
		$this->line(0.5, $y, 8, $y);
		$y += 1/32;
		$this->line(0.5, $y, 8, $y);
		$y += 1/16;

		// Invoice Total
		$this->setXY(0.5, $y);
		$this->cell(4.5, 4/16, 'Invoice Total:');

		$this->setXY(7, $y);
		$this->cell(1, 4/16, sprintf('$%.2f',$sub_total + $tax_total), null, null, 'R');

		// Account / Invoice Transactions
		$txn_list = $iv->getTransactions();
		$txn_total = 0;

		//$y += 1/16;
		//$this->line(0.5, $y, 8, $y);

		$y += 1/2;
		$this->setXY(0.5, $y);
		$this->line(0.5, $y, 8, $y);
		$this->cell(4.5, 4/16, 'Account Transactions:');
		$y += 1/4;
		// $this->setFont('', '', 12);
		foreach ($txn_list as $txn) {

			// $t = strip_tags(ImperiumView::niceDate($txn['date']));
			$t = $txn['date'];

			// Amounts > 0 Are Payments
			if ($txn['amount'] >= 0) {
				$t.= ' ' . $txn['note'];
				// $page->drawText(,36,$y);
			} else {
				$t.= ' Payment';
			}

			// Note
			$this->setXY(0.5, $y);
			$this->cell(4.5, 1/2, $t);
			// $this->drawText($t,36,$y);

			// Amount
			$t = sprintf('$%.2f',$txn['amount']);
			// $w = $pdf->getTextWidthAscii($this->getFont(), $this->getFontSize(), $t);
			// $this->drawText($t,574 - $w,$y);
			$this->setXY(7, $y);
			$this->cell(1, 1/2, $t, null, null, 'R');

			// Add Sums
			$txn_total += $txn['amount'];

			$y += 1/4;
		}

//		// Double Line
//		$y -= 4;
//		$this->drawLine(36,$y,576,$y);
//		$y -= 2;
//		$this->drawLine(36,$y,576,$y);
//		$y -= 4;

		// Balance
		$this->setXY(0.5, 9);
		$this->cell(4.5, 1/2, 'Balance:');

//		$y -= 12;
//		$this->drawText('Balance:',36,$y);
//		$t = sprintf('$%.2f',$txn_total);
//		$w = $this->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
//		$this->drawText($t,574 - $w,$y);

//		// Footer Line
		$y = 9;
		$this->line(0.5, $y, 8, $y);

		// Footer Text
//		$text = explode('\n',$_ENV['invoice']['foot_note']); // Split on Literal \n
//		foreach ($text as $line) {
//			$this->drawText($line,39,$y);
//			$y -= 10;
//		}

		// Footer Summary
		if ($iv['bill_amount'] ==$iv['paid_amount']) {
			$this->setXY(7, 9);
			$this->setTextColor(255, 0, 0);
			$this->cell(1, 1/2, 'Paid', null, null, 'R');
		} else {
			//$this->SetFont('Arial','B',12);
			//$this->SetTextColor(255,0,0);
			//$this->Cell(1,PDFDocument::PDF_LH_12,'Paid in Full','BRT',null,'R');
			$this->Cell(1,PDFDocument::PDF_LH_12,'$'.number_format($this->_invoice->bill_amount - $this->_invoice->paid_amount,2),'BRT',null,'R');
		}
	}
}
