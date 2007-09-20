<?php
require_once dirname(__FILE__) . '/../../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/shared/pdf/PdfMaker.php';

class PdfMakerTest extends PHPUnit_Framework_TestCase
{
    function createPdf()
    {
        return new PdfMaker();
    }

    //////////////////////////////////////////////////////

    function testConstruct()
    {
        $pdf = $this->createPdf();
        $this->assertTrue(is_object($pdf));
    }

    function testStandardValues()
    {
        $pdf = $this->createPdf();
        $this->assertEquals(50, $pdf->get('margin_top'));
        $this->assertEquals(42, $pdf->get('margin_right'));
        $this->assertEquals(50, $pdf->get('margin_bottom'));
        $this->assertEquals(42, $pdf->get('margin_left'));
    }

}

?>