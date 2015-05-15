<?php
/**
	Imperium PDF Documents

	@copyright 2006 Edoceo, Inc
	@package edoceo-imperium
	@link http://imperium.edoceo.com
*/

namespace Edoceo\Imperium\PDF;

class Template
{
	public $logo_file;
	public $logo_x;
	public $logo_y;

	public $title_text;
	public $title_x;
	public $title_y;

	public $note_text;
	public $note_x;
	public $note_y;

	public $date_text;
	public $date_x;
	public $date_y;

	public $from_address_text;
	public $from_address_w = 3.0;
	public $from_address_x = 0.5;
	public $from_address_y = 0.75;

	public $recv_address_text;
	public $recv_address_w = 3.5;
	public $recv_address_x = 0.5;
	public $recv_address_y = 2.375;

	public $ship_address_text;
	public $ship_address_w = 3.5;
	public $ship_address_x = 4.5;
	public $ship_address_y = 2.5;
}

class Base extends \TCPDF
{
	const FONT_FIXED = 'courier';
	const FONT_SANS  = 'helvetica';

	//const LH_10 = .175;
	//const LH_12 = .200;
	//const LH_14 = .225;
	//const LH_16 = .250;

	// const FONT_H = 1/72;

	//const FULL_WIDTH = 7.50;

	//public $_pt;
    //
	//private $_logo_file;
	//private $_recv_address;
	//private $_send_address;
	//private $_ship_address;
	//private $_note;

	protected $_time;
	protected $_title;

	/**
		Constructor
	*/
	function __construct($orientation='P', $unit='in', $format='LETTER', $unicode=true, $encoding='utf-8', $diskcache=false, $pdfa=false)
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

		// set document information
		$this->setAuthor(APP_NAME);
		$this->setCreator(APP_NAME);
		// $this->properties['Producer'] = 'Edoceo Imperium';
		// $this->setKeywords($this->name);
		//$pdf->properties['Keywords'] = 'Edoceo Imperium';
		//$pdf->properties['CreationDate']
		//$pdf->properties['ModDate'];
		//$pdf->properties['Trapped']

		// set margins
		$this->setMargins(0, 0, 0, true);
		$this->setHeaderFont(array(self::FONT_SANS, '', 12));
		$this->setHeaderMargin(0);
		$this->setPrintHeader(false);

		$this->setFooterMargin(0);
		//$this->setPrintFooter(false);

		// set auto page breaks
		$this->setAutoPageBreak(true, 1/2);
		//

		// set image scale factor
		$this->setImageScale(0);

		// set default font subsetting mode
		$this->setFontSubsetting(true);

		$this->setFont(self::FONT_SANS, '', 14);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		//$this->addPage();

	}
	
	function setTime($t)
	{
		$this->_time = $t;
	}

	function setTitle($x)
	{
		$this->_title = $x;
		parent::setSubject($x);
		parent::setTitle($x);
	}

	///**
	//	ImperiumPDF getTextWidthAscii
	//	This measures the width of some text
	//*/
	//function getTextWidthAscii($font,$size,$text)
	//{
	//	//$font = $this->getFont();
	//	$l = strlen($text);
	//	$w = 0;
	//	for ($i=0; $i < $l; $i++) {
	//	  $g = $font->glyphNumberForCharacter(ord($text[$i]));
	//	  $w += $font->widthForGlyph($g);
	//	}
	//	$u = $font->getUnitsPerEm();
	//	//$s = $this->getFontSize();
	//	return ($w / $u) * $size;
	//}
	///**
	//	ImperiumPDF getTextWidth
	//	This measures the width of some text
	//*/
	//// Was written with $this being a Pdf_Page, need to fix
	//// Some code references this in the Zend object (which we hack!)
	//function getTextWidth($font,$size,$text)
	//{
	//	//$font = $this->getFont();
	//	//$size = $this->getFontSize();
    //
	//	$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
	//	$characters = array();
	//	for ($i = 0; $i < strlen($drawingString); $i++) {
	//	  $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
	//	}
	//	$glyphs = $font->glyphNumbersForCharacters($characters);
	//	$widths = $font->widthsForGlyphs($glyphs);
	//	$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $size;
	//	return $stringWidth;
	//}
    //
	//static function textWidth($string, $font, $fontSize)
	//{
	//	$drawingString = iconv('', 'UTF-16BE', $string);
	//	$characters = array();
	//	for ($i = 0; $i < strlen($drawingString); $i++) {
	//		$characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
	//	}
	//	$glyphs = $font->glyphNumbersForCharacters($characters);
	//	$widths = $font->widthsForGlyphs($glyphs);
	//	$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
	//	return $stringWidth;
	//}

	/**
		TCPDF Header Implementation
	*/
	function MyHeader()
	{
		// Logo
		$logo_file = sprintf('%s/webroot/img/logo.png', APP_ROOT);
		// if (!is_file($logo_file)) {
		//	 $ch = curl_init($_ENV['application']['logo']);
		//	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//	 $img = curl_exec($ch);
		//	 file_put_contents($img,$logo_file);
		// }
		//if (is_file($this->_logo_file)) $this->Image($this->_logo_file,3.5,.5,4.5);
		//else $this->cell(4,self::LH_12,$this->_logo_file);

		$this->image($logo_file, 5, 0.5, 3, 0);

		// Send Address
		// $this->setFont(self::FONT_SANS, 'B', 12);
		$y = 0.75;
		$this->setXY(0.5, $y);
		$this->cell(3, 3/16, $_ENV['company']['name']);
		$y += (3 / 16);

		$this->setXY(0.5, $y);
		$this->cell(3, 3/16, $_ENV['company']['address']);
		$y += (3 / 16);

		$this->setXY(0.5, $y);
		$this->cell(3, 3/16, $_ENV['company']['city'] . ', ' . $_ENV['company']['region'] . ' ' . $_ENV['company']['postal']);
		$y += (3 / 16);

		$this->setXY(0.5, $y);
		$this->cell(3, 3/16, $_ENV['company']['phone']);

		// Date
		// $this->setColor(0x33, 0x33, 0x33);
		// $this->setFont(self::FONT_SANS, '', 12);

		$t = 'Date: ' . date('d M Y', $this->_time);
		$this->setXY(6, 1.5);
		$this->cell(2, 1/4, $t, null, null, 'R');

		// Document Title
		$this->setFont(self::FONT_SANS, 'B', 16);
		$this->setXY(3.5, 1.5);
		$this->cell(2, 4/16, $this->_title);


		// Blue Line
		$this->setDrawColor(0x33,0x66,0x99);
		$this->setLineWidth(1/32);
		$this->line(0.5, 2, 8, 2);

		// @note: this makes a small mark that shows where to fold
		$this->setDrawColor(0x99, 0x99, 0x99);
		$this->setlinewidth(1/64);
		$this->line(0, 11/3, .125, 11/3);
		$this->line(0, 11/3*2, .125, 11/3*2);

		// Page
		// $this->cell('Page: 1', 468, 654 );
	}

  /*
  function Header()
  {
	// Send Address
	if ($this->_send_address)
	{
	  $this->SetLineWidth(.001);
	  $this->SetFont('Arial','B',12);
	  $this->SetXY($this->_pt->from_address_x,$this->_pt->from_address_y); // Starts at 1/2, 5/8 (for printing in window envelope)
	  $this->MultiCell($this->_pt->from_address_w,self::LH_12,$this->_send_address);
	}

	// Document Title
	$this->SetFont('Arial','B',16);
	$this->SetXY(3.5,1.25);
	$this->Cell(4.5,self::LH_16,$this->_title);

	// Date & Page
	if ($this->show_pgnr)
	{
	  $this->SetFont('Arial','B',12);
	  $this->SetXY(6.5,1.75);
	  $this->Cell(1.5,self::LH_12, 'Page: '.$this->pageno().' of {nb}',null,0,'R');
	}

		// Receive Address
		// todo: put shipping address right next to this at
		if (strlen($this->_recv_address)>0)
		{
			$this->SetXY($this->_pt->recv_address_x,$this->_pt->recv_address_y);
			$this->SetFont('Arial','B',12);
			$this->MultiCell($this->_pt->recv_address_w,self::LH_12,$this->_recv_address);
		}
		if (strlen($this->_ship_address)>0)
		{
			$this->SetXY(4.5,2.5);
			$this->SetFont('Arial','B',12);
			$this->MultiCell(3.5,self::LH_12,$this->_ship_address);
		}
	/*
		if (strlen($this->_note)>0)
		{
			// Box for extra stuff, don't know what yet
			//$this->Rect(4.5,2.25,3.5,1.25);
			$this->setfont('Arial','',10);
			$this->setxy(4.5,2.5);
			$this->multicell(3.5,self::LH_10,$this->_note);
		}
	// * /
	// note: this makes a small mark that shows where to fold
		$this->setxy(.5,3.75); $this->setlinewidth(.001); $this->line(0,11/3,.125,11/3);
  }
  */
}
