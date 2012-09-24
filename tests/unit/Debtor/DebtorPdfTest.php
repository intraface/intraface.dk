<?php
require_once 'Intraface/modules/debtor/Visitor/Pdf.php';
require_once dirname(__FILE__) .'/stubs/Debtor.php';
require_once dirname(__FILE__) .'/stubs/DebtorLongProductText.php';
require_once dirname(__FILE__) .'/../Contact/stubs/Contact.php';
require_once dirname(__FILE__) .'/../Contact/stubs/ContactPerson.php';
require_once dirname(__FILE__) . '/../Stub/Fake/Ilib/Variable/Float.php';

class DebtorPdfTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        $this->debtor_pdf_path = TEST_PATH_TEMP . '/debtor.pdf';
        $this->tearDown();
    }

    function tearDown()
    {
        if (file_exists(TEST_PATH_TEMP.'debtor.pdf')) {
            @unlink($this->debtor_pdf_path);
        }
    }

    function createPdf()
    {
        return new Intraface_modules_debtor_Visitor_Pdf(new Stub_Translation);
    }

    function createDebtor()
    {
        $debtor = new FakeDebtor();
        $debtor->contact = new FakeContact;
        $debtor->contact->address = new Stub_Address;
        $debtor->contact_person = new FakeContactPerson;
        return $debtor;
    }

    function createDebtorLongProductText()
    {
        $debtor = new FakeDebtorLongProductText();
        $debtor->contact = new FakeContact;
        $debtor->contact->address = new Stub_Address;
        $debtor->contact_person = new FakeContactPerson;
        return $debtor;
    }

    function testConstruct()
    {
        $pdf = $this->createPdf();
        $this->assertTrue(is_object($pdf));
    }

    function testVisit()
    {
        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $pdf->visit($debtor);
        $pdf->output('file', $this->debtor_pdf_path);
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor.pdf', 1);
        $actual = file_get_contents($this->debtor_pdf_path);
        $this->assertEquals(strlen($expected), strlen($actual));
    }

    function testVisitWithPayment()
    {
        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $debtor->values['payment_total'] = 2125;
        $pdf->visit($debtor);
        $pdf->output('file', $this->debtor_pdf_path);
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor_with_payment.pdf', 1);
        $actual = file_get_contents($this->debtor_pdf_path);
        $this->assertEquals(strlen($expected), strlen($actual));
    }

    function testVisitWithLongProductText()
    {
        $pdf = $this->createPdf();
        $debtor = $this->createDebtorLongProductText();
        $pdf->visit($debtor);
        $pdf->output('file', $this->debtor_pdf_path);
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor_with_long_text.pdf', 1);
        $actual = file_get_contents($this->debtor_pdf_path);
        $this->assertEquals(strlen($expected), strlen($actual));
    }

    /*
    function testVisitWithOnlinePayment()
    {
        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $debtor->values['payment_online'] = 2125;
        $pdf->visit($debtor);
        $pdf->output('file', $this->debtor_pdf_path);
        $expected = file_get_contents('tests/unit/debtor/expected_debtor_with_payment.pdf', 1);
        $actual = file_get_contents($this->debtor_pdf_path);
        $this->assertEquals(strlen($expected), strlen($actual));
    }
    */
}

