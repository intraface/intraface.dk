<?php
/**
 * PdfMaker for Intraface
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
class Intraface_Pdf extends Document_Cpdf
{
    protected $value;
    protected $page;
    protected $page_height;
    protected $page_width;

    public function __construct()
    {
        $this->page_width = 595;
        $this->page_height = 841;

        // Predefined values.
        $this->value['margin_top'] = 50;
        $this->value['margin_right'] = 42;
        $this->value['margin_left'] = 42; // From 0 to the edge in the left side
        $this->value['margin_bottom'] = 50;

        $this->value['header_height'] = 51;
        $this->value['header_margin_top'] = 20;
        $this->value['header_margin_bottom'] = 20;

        $this->value['font_size'] = 11;
        $this->value['font_padding_top'] = 1;
        $this->value['font_padding_bottom'] = 4;

        $this->page = 1;

        // Creates a new A4 document
        parent::__construct(array(0, 0, $this->page_width, $this->page_height));

        // Rewrites the placement of Danish characters æ, ø, å, Æ, Ø, Å
        // After the Cpdf documentation
        // Table for the characters placements can be found here: http://www.fingertipsoft.com/3dkbd/ansitable.html
        // Table for their names is found here: http://www.gust.org.pl/fonty/qx-table2.htm
        // Notice that the placement of the characters are different in the two tables. Placement is correct in the first.
        
        $diff = array(230 => 'ae',
                      198 => 'AE',
                      248 => 'oslash',
                      216 => 'Oslash',
                      229 => 'aring',
                      197 => 'Aring');

        parent::selectFont('Helvetica.afm', array('differences' => $diff));

        $this->calculateDynamicValues();
    }

    /**
     * Calculates all the dynamic values
     * Notice that X and Y are reset.
     *
     * @return void
     */
    private function calculateDynamicValues()
    {
        // Sets values based on the predefined values.
        $this->value['right_margin_position'] = $this->page_width - $this->value['margin_right']; // content_width from 0 to right margen        
        $this->value['top_margin_position'] = $this->page_height - $this->value['margin_top']; // content_height

        $this->value['content_width'] = $this->page_width - $this->value['margin_right'] - $this->value['margin_left']; // content_width fom 0 to right margen
        $this->value['content_height'] = $this->page_height - $this->value['margin_bottom'] - $this->value['margin_top']; // content_height

        $this->value['font_spacing'] = $this->value['font_size'] + $this->value['font_padding_top'] + $this->value['font_padding_bottom'];

        // X and Y are need to be reset, if the margins are changed.
        $this->setX(0);
        $this->setY(0);
    }

    /**
     * Sets value
     *
     * @param integer $key   The key to set
     * @param integer $value The value to put in the key
     *
     * @return void
     */
    public function setValue($key, $value)
    {
        $this->value[$key] = $value;
        // Every time we change a fixed value we need to update the dynamic values
        if (in_array($key, array('margin_right', 'margin_left', 'margin_top', 'margin_bottom', 'font_size', 'font_padding_top', 'font_padding_bottom'))) {
            $this->calculateDynamicValues();
        }
    }

    /**
     * Sets the x
     *
     * @param integer $value The x for the page
     *
     * @return void
     */
    public function setX($value)
    {
        if (is_int($value)) {
            $this->value['x'] = $this->get('margin_left') + $value;
        } elseif (is_string($value) && substr($value, 0, 1) == "+") {
            $this->value['x'] +=  intval(substr($value, 1));
        } elseif (is_string($value) && substr($value, 0, 1) == "-") {
            $this->value['x'] -= intval(substr($value, 1));
        } else {
            throw new Exception('Ugyldig værdi i setX: '.$value);
        }
    }

    /**
     * Sets the y
     *
     * @param integer $value The y for the page
     *
     * @return void
     */
    public function setY($value)
    {
        if (is_int($value)) {
            $this->value['y'] = $this->page_height - $this->get('margin_top') - $value;
        } elseif (is_string($value) && substr($value, 0, 1) == "+") {
            $this->value['y'] += intval(substr($value, 1));
        } elseif (is_string($value) && substr($value, 0, 1) == "-") {
            $this->value['y'] -= intval(substr($value, 1));
        } else {
            throw new Exception("Ugyldig værdi i setY: ".$value);
        }
    }

    /**
     * Adds the header to the document
     *
     * @param string $headerImg The filepath for the header image
     *
     * @return void
     */
    public function addHeader($headerImg = '')
    {
        if (!file_exists($headerImg)) {
            return false;
        }

        $header = parent::openObject();
        $size = getImageSize($headerImg); // array(0 => width, 1 => height)

        $height = $this->get('header_height');;
        $width = $size[0] * ($height/$size[1]);

        if ($width > $this->get('content_width')) {
            $width = $this->get('content_width');
            $height = $size[1] * ($width/$size[0]);
        }
        parent::addJpegFromFile($headerImg, $this->get('right_margin_position') - $width, $this->page_height - $this->get('header_margin_top') - $height, $width, $height); // , ($this->value["page_width"] - $this->value["margin_left"])/10
        parent::closeObject();
        parent::addObject($header, "all");
        $this->setValue('margin_top', $height + $this->get('header_margin_top') + $this->get('header_margin_bottom'));
        $this->setY(0);
    }

   /**
     * create a round rectangle
     *
     * @param integer $x      The starting x point
     * @param integer $y      The starting y point
     * @param integer $width  The width of the rectangle
     * @param integer $height The height of the rectangle
     * @param integer $round  How much to round the rectangle
     *
     * @return void
     */
    public function roundRectangle($x, $y, $width, $height, $round)
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

   /**
     * write the document to a file
     *
     * @param string $data The data to write
     *
     * @return void
     */
    public function writeDocument($data, $filnavn)
    {
        $file = fopen($filnavn, 'wb');
        fwrite($file, $data);
        fclose($file);
    }

    function addText($x, $y, $size, $text, $angle = 0, $wordSpaceAdjust = 0)
    {
        $text = utf8_decode($text);
        parent::addText($x, $y, $size, $text, $angle = 0, $wordSpaceAdjust = 0);
    }

    /**
     * Changes to next page
     *
     * @param boolean $sub_text What is the sub text
     *
     * @return integer the new y
     */
    public function nextPage($sub_text = false)
    {
        if ($sub_text == true) {
            $this->addText($this->value['right_margin_position'] - $this->getTextWidth($this->value['font_size'], "<i>Fortsættes på næste side...</i>") - 30, $this->value["margin_bottom"] - $this->value['font_padding_top'] - $this->value['font_size'], $this->value['font_size'], "<i>Fortsættes på næste side...</i>");
        }
        parent::newPage();
        $this->setY(0);
        $this->page++;
        return $this->get('y');
    }

    /**
     * Get values
     *
     * @param string $key Which string to get
     *
     * @return mixed
     */
    public function get($key = '')
    {
        if (!empty($key)) {
            return($this->value[$key]);
        } else {
            return $this->value;
        }
    }
}