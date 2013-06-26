<?php
/**
    Imperium PDF Documents

    @copyright    2008 Edoceo, Inc
  @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

  @see        http://framework.zend.com/apidoc/core/Zend_Pdf/Zend_Pdf.html
*/

class PDFTemplate
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

class ImperiumPDF extends Zend_Pdf
{
    const LH_10 = .175;
    const LH_12 = .200;
    const LH_14 = .225;
    const LH_16 = .250;

    const FULL_WIDTH = 7.50;

    public $_pt;

    private $_date;
    private $_logo_file;
    private $_recv_address;
    private $_send_address;
    private $_ship_address;
    private $_note;
    private $_title;

    public $show_pgnr = true;

    /**
        Constructor
    */
    function __construct($name)
    {
        parent::__construct(null,null,false);
        //$pdf = new Zend_Pdf();
        $this->properties['Author'] = 'Edoceo Imperium';
        $this->properties['Creator'] = 'Edoceo Imperium';
        $this->properties['Producer'] = 'Edoceo Imperium';
        $this->properties['Subject'] = $name;
        $this->properties['Title'] = $name;
        //$pdf->properties['Keywords'] = 'Edoceo Imperium';
        //$pdf->properties['CreationDate']
        //$pdf->properties['ModDate'];
        //$pdf->properties['Trapped']
    }

    /**
        ImperiumPDF getTextWidthAscii
        This measures the width of some text
    */
    function getTextWidthAscii($font,$size,$text)
    {
        //$font = $this->getFont();
        $l = strlen($text);
        $w = 0;
        for ($i=0; $i < $l; $i++) {
          $g = $font->glyphNumberForCharacter(ord($text[$i]));
          $w += $font->widthForGlyph($g);
        }
        $u = $font->getUnitsPerEm();
        //$s = $this->getFontSize();
        return ($w / $u) * $size;
    }
    /**
        ImperiumPDF getTextWidth
        This measures the width of some text
    */
    // Was written with $this being a Pdf_Page, need to fix
    // Some code references this in the Zend object (which we hack!)
    function getTextWidth($font,$size,$text)
    {
        //$font = $this->getFont();
        //$size = $this->getFontSize();

        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
          $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $size;
        return $stringWidth;
    }

    static function textWidth($string, $font, $fontSize)
    {
        $drawingString = iconv('', 'UTF-16BE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
    /**
    */
    function newPage($size,$opt=null)
    {
        $page = parent::newPage($size,$opt);

        // Logo
        $logo_file = APP_ROOT . '/webroot/img/logo.png';
        // if (!is_file($logo_file)) {
        //     $ch = curl_init($_ENV['application']['logo']);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     $img = curl_exec($ch);
        //     file_put_contents($img,$logo_file);
        // }
        //if (is_file($this->_logo_file)) $this->Image($this->_logo_file,3.5,.5,4.5);
        //else $this->cell(4,self::LH_12,$this->_logo_file);
        $t = 756;
        $b = $t - 48;
        $r = 578;
        $l = $r - 256;
        $page->drawImage(Zend_Pdf_Image::imageWithPath($logo_file),$l,$b,$r,$t);

        // Blue Line
        $page->setLineColor( new Zend_Pdf_Color_Html('#1a6293') );
        $page->setLineWidth(3);
        $page->drawLine(36,648,576,648);

        // Send Address
        $font_hb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($font_hb,12);
        $page->drawText($_ENV['company']['name'],36,720);
        $page->drawText($_ENV['company']['address'],36,708);
        $page->drawText($_ENV['company']['city'] . ', ' . $_ENV['company']['region'] . ' ' . $_ENV['company']['postal'],36,696);
        $page->drawText($_ENV['company']['phone'],36,684);

        // Document Title
        $page->setFont($font_hb,16);
        $page->drawText($this->properties['Title'], 252, 684);

        // Page
        $page->drawText('Page: 1', 468, 654 );

        return $page;
    }

  /*
  function Header()
  {
    // Blue Line
    $this->SetDrawColor(16,48,80);
    $this->SetLineWidth(.0625);
    $this->Line(.5,2,8,2);

    // Send Address
    if ($this->_send_address)
    {
      $this->SetLineWidth(.001);
      $this->SetDrawColor(0,0,0);
      $this->SetFont('Arial','B',12);
      $this->SetXY($this->_pt->from_address_x,$this->_pt->from_address_y); // Starts at 1/2, 5/8 (for printing in window envelope)
      $this->MultiCell($this->_pt->from_address_w,self::LH_12,$this->_send_address);
    }

    // Place Logo
    if (is_file($this->_logo_file)) $this->Image($this->_logo_file,3.5,.5,4.5);
    else $this->cell(4,self::LH_12,$this->_logo_file);

    // Document Title
    $this->SetFont('Arial','B',16);
    $this->SetXY(3.5,1.25);
    $this->Cell(4.5,self::LH_16,$this->_title);

    // Date & Page
    if ($this->_date)
    {
      $this->SetFont('Arial','B',12);
      $this->SetXY(3.5,1.5);
      $this->cell(2,self::LH_12, 'Date: '.date('d M Y',strtotime($this->_date)));
    }
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
