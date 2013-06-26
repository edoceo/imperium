<?php
/**
    Get as PDF

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

class InvoicePDF
{

    private $_pdf;

    private $_page_c = 1;

    function __construct($iv)
    {

        $co = new Contact($iv->contact_id);
        $ba = new ContactAddress($iv->bill_address_id);
        $sa = new ContactAddress($iv->ship_address_id);

        $pdf = new ImperiumPDF('Invoice #' . $iv->id);

        //$font = Zend_Pdf_Font::fontWithPath(APP_ROOT . '/var/fonts/Edoceo-MICR.ttf');
        $font_h = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $font_hb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

        // Date
        $page->setFont($font_hb,12);
        $page->drawText('Date: '.date('d M Y',strtotime($iv->date)), 468, 684 );

        // Bill Address
        $y = 612;
        $page->setFont($font_h,12);
        $page->drawText('Bill To:',36,$y);
        $page->drawText('Ship To:',306,$y);

        $page->setFont($font_hb,12);
        $page->drawText($co->name, 74, $y);
        $y -= 12;
        $a = explode("\n",$ba->address);
        foreach ($a as $t) {
            $page->drawText($t, 74, $y);
            $y-=12;
        }

        // Ship Address
        if (strlen($sa->address)) {
            $y = 612;
            $page->drawText($co->name, 352, $y);
            $y -= 12;
            $page->drawText($sa->address, 352, $y);
        }

        $y -= 12;
        $page->setFont($font_h,12);
        $page->drawText('Notes:',36,$y);
        $page->setFont($font_hb,12);
        $page->drawText($iv->note,74,$y);

        // @note: this makes a small mark that shows where to fold
        //$this->setxy(.5,3.75); $this->setlinewidth(.001); $this->line(0,11/3,.125,11/3);

        // Column Headers
        // 528 is one third of the page
        $y = 528;
        $page->setFont($font_hb,14);
        // Note
        $page->drawText('Note', 36, $y);
        // Quantity
        $t = 'Quantity';
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t) / 2;
        // @todo why does this one need a 20 point hack?
        $w-= 20;
        $page->drawText($t, 364 + $w, $y);
        // Price
        $t = 'Price';
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t) / 2;
        $page->drawText($t, 436 + $w, $y);
        // Total
        $t = 'Total';
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t) / 2;
        $page->drawText($t, 508 + $w, $y);

        $y -= 2;
        // Blue Line Below
        $page->setLineWidth(1);
        $page->drawLine(36,$y,576,$y);
        // Column LInes
        $page->setLineWidth(.5);
        $page->drawLine(364,$y,364,94);
        $page->drawLine(436,$y,436,94);
        $page->drawLine(508,$y,508,94);
        //$page->drawLine(576,$y,576,94);

        $y -= 14;

        // Items Table
        $sub_total = 0;
        $tax_total = 0;
        $ivi_list = $iv->getInvoiceItems();
        foreach ($ivi_list as $ivi) {

            $dy = $y;

            $page->setFont($font_h,12);

            // Multi Line Name
            $page->drawText(substr(trim($ivi->name),0,55),36,$dy);
            /*
            $lines = explode("\n",wordwrap(stripslashes($ivi->name),55));
            foreach ($lines as $line) {
                $dy -= 12;
                $page->drawText($dy . '-'. $line,36,$dy);
            }
            */

            //$page->drawText($ivi->note,32,$y);
            $t = sprintf('%.2f',$ivi->quantity);
            $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
            $page->drawText($t,364 + $w,$y);

            $t = sprintf('%.2f',$ivi->rate) .  '/' . $ivi->unit;
            $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
            $page->drawText($t,506 - $w,$y);

            $t = sprintf('%.2f',$ivi->quantity * $ivi->rate);
            $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
            $page->drawText($t,574 - $w,$y);
            //$page->drawText($quantity,504,$y);
            $y -= 12;

            if ($y <= 108) {
                $pdf->pages[] = $page;
                $y = 760;
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            }

            // Add Sums
            $sub_total += ($ivi->quantity * $ivi->rate);
            $tax_total += ( ($ivi->quantity * $ivi->rate) * floatval($ivi->tax_rate) );
        }
        $page->setLineWidth(.5);

        // Single Line
        $y += 8;
        $page->drawLine(36,$y,576,$y);

        // Sub Total Line
        $y -= 14;
        $page->setFont($font_hb,12);
        $page->drawText('Sub Total:',36,$y);
        $t = sprintf('$%.2f',$sub_total);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
        $page->drawText($t,574 - $w,$y);

        // Tax Total Line
        $y -= 14;
        $page->drawText('Sales Tax:',36,$y);
        $t = sprintf('$%.2f',$tax_total);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
        $page->drawText($t,574 - $w,$y);

        // Double Line
        $y -= 4;
        $page->drawLine(36,$y,576,$y);
        $y -= 2;
        $page->drawLine(36,$y,576,$y);
        $y -= 4;

        // Invoice Total
        $y -= 12;
        $page->drawText('Invoice Total:',36,$y);
        $t = sprintf('$%.2f',$sub_total + $tax_total);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
        $page->drawText($t,574 - $w,$y);

        // Account / Invoice Transactions
        $txn_list = $iv->getTransactions();
        $txn_total = 0;

        $y -= 12;
        $page->drawLine(36,$y,576,$y);
        $y -= 12;
        $page->drawText('Account Transactions:',36,$y);
        $page->setFont($font_h,12);
        foreach ($txn_list as $txn) {

            $y -= 12;

            $t = strip_tags(ImperiumView::niceDate($txn->date));

            // Amounts > 0 Are Payments
            if ($txn->amount >= 0) {
                $t.= ' ' . $txn->note;
                // $page->drawText(,36,$y);
            } else {
                $t.= ' Payment';
            }

            // Note
            $page->drawText($t,36,$y);

            // Amount
            $t = sprintf('$%.2f',$txn->amount);
            $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
            $page->drawText($t,574 - $w,$y);

            // Add Sums
            $txn_total += $txn->amount;
        }

        // Double Line
        $y -= 4;
        $page->drawLine(36,$y,576,$y);
        $y -= 2;
        $page->drawLine(36,$y,576,$y);
        $y -= 4;

        // Balance
        $y -= 12;
        $page->drawText('Balance:',36,$y);
        $t = sprintf('$%.2f',$txn_total);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
        $page->drawText($t,574 - $w,$y);

        // Footer Line
        $page->drawLine(36,94,576,94);

        // Footer Text
        $y = 81;
        $page->setFont($font_h,10);
        $text = explode('\n',$_ENV['invoice']['foot_note']); // Split on Literal \n
        foreach ($text as $line) {
            $page->drawText($line,39,$y);
            $y -= 10;
        }

        // Footer Summary
        /*
        $page->setLineWidth(.5);
        $page->setLineColor( new Zend_Pdf_Color_Html('#000000') );
        $page->drawRectangle(436,94,576,54,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->drawLine(436,80,576,80);
        $page->drawLine(436,67,576,67);

        $page->setFont($font_h,12);
        $page->drawText('Sub Total:',437,81);
        $page->drawText('Sales Tax:',437,68);
        $page->drawText('Total:',437,55);

        $t = '$' . number_format($iv->sub_total,2);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$t);
        $page->drawText($t,575 - $w,81);

        $x = '$' . number_format($iv->tax_total,2);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$x);
        $page->drawText($x,575 - $w,68);

        $x = '$' . number_format($iv->tax_total + $iv->sub_total,2);
        $w = $pdf->getTextWidthAscii($page->getFont(),$page->getFontSize(),$x);
        $page->drawText($x,575 - $w,55);

        /*
        if ($this->_invoice->bill_amount==$this->_invoice->paid_amount)
        {
            $this->SetFont('Arial','B',12);
            $this->SetTextColor(255,0,0);
            $this->Cell(1,PDFDocument::PDF_LH_12,'Paid in Full','BRT',null,'R');
        } else {
            $this->Cell(1,PDFDocument::PDF_LH_12,'$'.number_format($this->_invoice->bill_amount - $this->_invoice->paid_amount,2),'BRT',null,'R');
        }
        */

        // Add Page
        $pdf->pages[] = $page;
        $this->_pdf = $pdf;

    }
    /**
    */
    function render()
    {
        return $this->_pdf->render();
    }
}
