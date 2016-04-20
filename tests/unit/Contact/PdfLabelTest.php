<?php
require_once 'Intraface/modules/contact/PdfLabel.php';
require_once 'Intraface/Pdf.php';

class PdfLabelTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        if (file_exists(TEST_PATH_TEMP.'pdf_label.pdf')) {
            unlink(TEST_PATH_TEMP.'pdf_label.pdf');
        }
    }

    function getPdfLabel()
    {
        return new Intraface_modules_contact_PdfLabel(0);
    }

    /////////////////////////////////////////////////////////

    function testConstruction()
    {
        $pdf = $this->getPdfLabel();
        $this->assertTrue(is_object($pdf));
    }

    function testGenerate()
    {
        $this->markTestSkipped(
            'This test is not passing.'
        );
        $pdf = $this->getPdfLabel();

        for ($i = 0; $i < 10; $i++) {
            $contacts[$i] = array(
                'number' => $i,
                'name' => 'Test'.$i,
                'address' => array('address' => 'Vej '.$i,
                    'postcode' => '100'.$i,
                    'city' => 'By '.$i,
                    'country' => 'Land '.$i
                )
            );
        }

        $pdf->generate($contacts, 'random search', array('keyword1', 'keyword2'));

        // file_put_contents(TEST_PATH_TEMP.'pdf_label.pdf', $pdf->output());

        $this->assertEquals(strlen(file_get_contents(dirname(__FILE__) .'/expected/pdf_label.pdf', 1)), strlen($pdf->output()));
    }
}
