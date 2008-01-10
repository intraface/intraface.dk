<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/debtor/Visitor/Pdf.php';
require_once '../stubs/Translation.php';
require_once '../stubs/Address.php';
require_once '../contact/stubs/Contact.php';
require_once '../contact/stubs/ContactPerson.php';
require_once 'stubs/Debtor.php';


class DebtorPdfTest extends PHPUnit_Framework_TestCase
{
    function setup() {
        
        if(file_exists(TEST_PATH_TEMP.'debtor.pdf')) {
            unlink(TEST_PATH_TEMP.'debtor.pdf');
        }
    
    }
    
    function createPdf()
    {
        return new Debtor_Report_Pdf(new FakeTranslation);
    }
    
    function createDebtor() {
        $debtor = new FakeDebtor();
        $debtor->contact = new FakeContact;
        $debtor->contact->address = new FakeAddress;
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
        $pdf->visit($this->createDebtor());
        $pdf->output('file', TEST_PATH_TEMP.'debtor.pdf');
        $this->assertEquals(file_get_contents('expected_debtor.pdf'), file_get_contents(TEST_PATH_TEMP.'debtor.pdf'));
        $this->assertTrue(is_string($pdf->output('string')));
    }

}

?>