<?php
class PdfMakerTest extends PHPUnit_Framework_TestCase
{
    function createPdf()
    {
        return new Intraface_Pdf();
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
