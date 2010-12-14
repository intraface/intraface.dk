<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'Intraface/modules/debtor/Visitor/Pdf.php';
require_once dirname(__FILE__) .'/stubs/Debtor.php';
require_once dirname(__FILE__) .'/stubs/DebtorLongProductText.php';
require_once dirname(__FILE__) .'/../Contact/stubs/Contact.php';
require_once dirname(__FILE__) .'/../Contact/stubs/ContactPerson.php';

class DebtorPdfTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        $this->tearDown();
    }

    function tearDown()
    {
        if (file_exists(TEST_PATH_TEMP.'debtor.pdf')) {
            unlink(TEST_PATH_TEMP.'debtor.pdf');
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
        error_reporting(E_ALL);

        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $pdf->visit($debtor);
        $pdf->output('file', TEST_PATH_TEMP.'debtor.pdf');
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor.pdf', 1);
        $actual = file_get_contents(TEST_PATH_TEMP.'debtor.pdf');


        $this->assertEquals(strlen($expected), strlen($actual));
    }

    function testVisitWithPayment()
    {
        error_reporting(E_ALL);

        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $debtor->values['payment_total'] = 2125;
        $pdf->visit($debtor);
        $pdf->output('file', TEST_PATH_TEMP.'debtor.pdf');
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor_with_payment.pdf', 1);
        $actual = file_get_contents(TEST_PATH_TEMP.'debtor.pdf');


        $this->assertEquals(strlen($expected), strlen($actual));
    }

    function testVisitWithLongProductText()
    {
        error_reporting(E_ALL);

        $pdf = $this->createPdf();
        $debtor = $this->createDebtorLongProductText();
        $pdf->visit($debtor);
        $pdf->output('file', TEST_PATH_TEMP.'debtor.pdf');
        $expected = file_get_contents(dirname(__FILE__) .'/expected_debtor_with_long_text.pdf', 1);
        $actual = file_get_contents(TEST_PATH_TEMP.'debtor.pdf');


        $this->assertEquals(strlen($expected), strlen($actual));
    }

    /*
    function testVisitWithOnlinePayment()
    {
        error_reporting(E_ALL);

        $pdf = $this->createPdf();
        $debtor = $this->createDebtor();
        $debtor->values['payment_online'] = 2125;
        $pdf->visit($debtor);
        $pdf->output('file', TEST_PATH_TEMP.'debtor.pdf');
        $expected = file_get_contents('tests/unit/debtor/expected_debtor_with_payment.pdf', 1);
        $actual = file_get_contents(TEST_PATH_TEMP.'debtor.pdf');


        $this->assertEquals(strlen($expected), strlen($actual));
    }
    */

}

?>