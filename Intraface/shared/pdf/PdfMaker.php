<?php
/**
 * PdfMaker for Intraface
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
require_once 'Cpdf/class.pdf.php';

class PdfMaker extends Cpdf
{
    var $value;
    var $page;
    var $kernel;
    var $page_height;
    var $page_width;

    function __construct(/*$kernel*/)
    {
        /*
        if(!is_object($kernel) || strtolower(get_class($kernel)) != 'kernel') {
            trigger_error("Første parameter er ikke kernel i PdfMaker->__construct", E_USER_ERROR);
        }

        $this->kernel = $kernel;
        */

        $this->page_width = 595;
        $this->page_height = 841;

        // Foruddefineret værdier
        $this->value['margin_top'] = 50;
        $this->value['margin_right'] = 42;
        $this->value['margin_left'] = 42; // Fra 0 til kanten i venstre side
        $this->value['margin_bottom'] = 50;

        $this->value['header_height'] = 51;
        $this->value['header_margin_top'] = 20;
        $this->value['header_margin_bottom'] = 20;

        $this->value['font_size'] = 11;
        $this->value['font_padding_top'] = 1;
        $this->value['font_padding_bottom'] = 4;

        $this->page = 1;

        // Sætter værdier på baggrund af faste værdier
        $this->value['right_margin_position'] = $this->page_width - $this->value['margin_right']; // content_width fra 0 til højre-margin
        $this->value['top_margin_position'] = $this->page_height - $this->value['margin_top']; // content_height

        $this->value['content_width'] = $this->page_width - $this->value['margin_right'] - $this->value['margin_left']; // content_width fra 0 til højre-margin
        $this->value['content_height'] = $this->page_height - $this->value['margin_bottom'] - $this->value['margin_top']; // content_height

        $this->value['font_spacing'] = $this->value["font_size"] + $this->value['font_padding_top'] + $this->value['font_padding_bottom'];

        // Opretter en nyt A4 dokument
        parent::Cpdf(array(0, 0, $this->page_width, $this->page_height));

        // Omskrivning af placering på specielle tegn: æ, ø, å, Æ, Ø, Å
        // Efter Cpdf dokumentation
        // Tabel for tegnenes placering fundet her: http://www.fingertipsoft.com/3dkbd/ansitable.html
        // Tabel for deres navn fundet her: http://www.gust.org.pl/fonty/qx-table2.htm
        // Bemærk at placeringen af tegnene er forskellige fra de 2 tabeller. Den øverste har den rigtige placering.

        $diff = array(230 => 'ae',
                      198 => 'AE',
                      248 => 'oslash',
                      216 => 'Oslash',
                      229 => 'aring',
                      197 => 'Aring');

        // Hmm her burde lige laves en anden måde at tilgå stien på!
        //$shared_pdf = $this->kernel->useShared('pdf');

        //parent::selectFont(PATH_INCLUDE_SHARED.'pdf/fonts/Helvetica.afm', array('differences'=>$diff));
        parent::selectFont('Helvetica.afm', array('differences'=>$diff));
        $this->setX(0);
        $this->setY(0);
    }

    /**
     *
     */
    function start()
    {
    }

    function load()
    {
    }

    function setValue($key, $value)
    {
        $this->value[$key] = $value;
    }

    function setX($value)
    {

        if(is_int($value)) {
            $this->value['x'] = $this->get('margin_left') + $value;
        }
        elseif(is_string($value) && substr($value, 0, 1) == "+") {

            $this->value['x'] +=  intval(substr($value, 1));
        }
        elseif(is_string($value) && substr($value, 0, 1) == "-") {
            $this->value['x'] -= intval(substr($value, 1));
        }
        else {
            trigger_error('Ugyldig værdi i setX: '.$value, E_USER_ERROR);
        }
    }

    function setY($value)
    {

        if(is_int($value)) {

            $this->value['y'] = $this->page_height - $this->get('margin_top') - $value;
        }
        elseif(is_string($value) && substr($value, 0, 1) == "+") {
            $this->value['y'] += intval(substr($value, 1));
        }
        elseif(is_string($value) && substr($value, 0, 1) == "-") {

            $this->value['y'] -= intval(substr($value, 1));
        }
        else {
            trigger_error("Ugyldig værdi i setY: ".$value, E_USER_ERROR);
        }
    }

    function addHeader($headerImg = "")
    {

        if(file_exists($headerImg)) {
            $header = parent::openObject();
            $size = getImageSize($headerImg); // array(0 => width, 1 => height)

            $height = $this->get('header_height');;
            $width = $size[0] * ($height/$size[1]);

            if($width > $this->get('content_width')) {
                $width = $this->get('content_width');
                $height = $size[1] * ($width/$size[0]);
            }
            parent::addJpegFromFile($headerImg, $this->get('right_margin_position') - $width, $this->page_height - $this->get('header_margin_top') - $height, $width, $height); // , ($this->value["page_width"] - $this->value["margin_left"])/10

            parent::closeObject();

            parent::addObject($header, "all");

            $this->setValue('margin_top', $height + $this->get('header_margin_top') + $this->get('header_margin_bottom'));
            $this->setY(0);
        }
    }

    function roundRectangle($x, $y, $width, $height, $round)
    {
        parent::setLineStyle(1);
        parent::line($x, $y+$round, $x, $y+$height-$round);
        parent::line($x+$round, $y+$height, $x+$width-$round, $y+$height);
        parent::line($x+$width, $y+$height-$round, $x+$width, $y+$round-1);
        parent::line($x+$width-$round, $y, $x+$round, $y);

        parent::partEllipse($x+$round, $y+$round,180, 270, $round);
        parent::partEllipse($x+$round, $y+$height-$round, 90, 180, $round);
        parent::partEllipse($x+$width-$round, $y+$height-$round, 0, 90, $round);
        parent::partEllipse($x+$width-$round, $y+$round, 270, 360, $round);

    }

    function writeDocument($data, $filnavn)
    {
        //$file = fopen("files/".$filnavn, "wb");
        $file = fopen($filnavn, 'wb');
        fwrite($file, $data);
        fclose($file);
    }

    /**
     *
     */
    function nextPage($sub_text = false)
    {

        if($sub_text == true) {
            parent::addText($this->value['right_margin_position'] - parent::getTextWidth($this->value['font_size'], "<i>Fortsættes på næste side...</i>") - 30, $this->value["margin_bottom"] - $this->value['font_padding_top'] - $this->value['font_size'], $this->value['font_size'], "<i>Fortsættes på næste side...</i>");
        }
        parent::newPage();
        $this->setY(0);
        // $pointY = $this->value["page_height"] - 30;	// lige lidt afstand på næste side til starten
        $this->page++;
        return $this->get('y');
    }


    function get($key = '')
    {
        if(!empty($key)) {
            return($this->value[$key]);
        } else {
            return $this->value;
        }
    }
}
?>