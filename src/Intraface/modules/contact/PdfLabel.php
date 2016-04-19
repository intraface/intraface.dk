<?php

class Intraface_modules_contact_PdfLabel extends Intraface_Pdf
{
    private $label_width;
    private $label_height;
    private $label_padding_left;
    private $label_padding_top;
    
    public function __construct($label_type)
    {
        parent::__construct();
        $this->setValue('font_size', 10);
        
        switch ($label_type) {
            case 1:
                // 2x8 labels pr. ark
                $this->setValue('margin_top', 0); // x/2,83 = mm
                $this->setValue('margin_right', 0);
                $this->setValue('margin_bottom', 0);
                $this->setValue('margin_left', 0);
        
                $this->label_width = ceil($this->get('content_width')/2);
                $this->label_height = ceil($this->get('content_height')/8);
        
                $this->label_padding_left = 42;
                $this->label_padding_top = 28;
                break;
            default:
                // (case: 0)
                // 3x7 labels pr. A4 ark
                $this->setValue('margin_top', 42); // x/2,83 = mm
                $this->setValue('margin_right', 19);
                $this->setValue('margin_bottom', 42);
                $this->setValue('margin_left', 19);
        
                $this->label_width = ceil($this->get('content_width')/3);
                $this->label_height = ceil(($this->get('content_height'))/7);
        
                $this->label_padding_left = 14;
                $this->label_padding_top = 14;
                break;
        }
        
        
    }
    
    public function generate($contacts, $search, $keywords)
    {
        
        // Search info on first label
        $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top, $this->get('font_size'), "<b>Søgning</b>");
        $line = 1;
        if ($search != "") {
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing'), $this->get('font_size'), "Søgetekst: ".$search);
            $line++;
        }
        
        if (is_array($keywords) && count($keywords) > 0) {
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing') * $line, $this->get('font_size'), "Nøgleord: ".implode(", ", $keywords));
            $line++;
        }
        
        $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing') * $line, $this->get('font_size'), "Antal labels i søgning: ".count($contacts));
        
        // The contacts on labels
        
        for ($i = 0, $max = count($contacts); $i < $max; $i++) {
            // TODO -- hvorfor bruger vi ikke antallet af labels til at vide, hvorn�r
            // vi skifter linje? Vi kender faktisk ikke antallet af labels i en r�kke. Det kunne vi selvf�lgelig komme til
            if ($this->get('x') + $this->label_width  > $this->get('right_margin_position')) {
                // For enden af linjen, ny linje
                $this->setY("-".$this->label_height);
                $this->setX(0);
            } else {
                // Vi rykker en label til h�jre
                $this->setX("+".$this->label_width);
            }
        
        
            if ($this->get('y') - $this->label_height < $this->get('margin_bottom')) {
                // Hvis n�ste labelsr�kke ikke kan n� at v�re der tager vi en ny side.
                $this->newPage();
                $this->setX(0);
                $this->setY(0);
            }
        
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top, $this->get('font_size'), "<b>".$contacts[$i]['number']."</b>");
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing'), $this->get('font_size'), "<b>".$contacts[$i]['name']."</b>");
            $line = 2;
            $address_lines = explode("\n", $contacts[$i]['address']['address']);
            foreach ($address_lines as $l) {
                if (trim($l) != "") {
                    $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing') * $line, $this->get('font_size'), $l);
                    $line++;
                }
            }
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing') * $line, $this->get('font_size'), $contacts[$i]['address']['postcode']." ".$contacts[$i]['address']['city']);
            $line++;
            $this->addText($this->get('x') + $this->label_padding_left, $this->get('y') - $this->label_padding_top - $this->get('font_spacing') * $line, $this->get('font_size'), $contacts[$i]['address']['country']);
        }
    }
}
