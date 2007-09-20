<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/modules/debtor/Visitor/Pdf.php';

class FakeAddress {
    function get($key = '') {
        $info = array('name' => 'Lars Olesen', 'address' => 'Græsvangen 8, Syvsten', 'postcode' => 9300, 'city' => 'Aarhus N', 'cvr' => '', 'ean' => '', 'phone' => '75820811', 'email' => 'lars@legestue.net');
        if (empty($key)) return $info;
        else return $info[$key];
    }
}

class FakeContactPerson {
    function get() {}
}

class FakeContact
{
    public $address;
    function __construct()
    {
        $this->address = new FakeAddress;
    }
    function get() {
        return 'Contact Name';
    }
}

class FakeSetting {
    function get() {}
}

class FakeIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeAddress;
    }
    function get() {
        return array('name' => 'Intranetname', 'contact_person' => '');
    }
}

class FakeDebtor {
    public $kernel;
    public $contact;
    public $value = array('dk_this_date' => '2007-10-10', 'due_date' => '2007-10-10', 'dk_due_date' => '2007-10-10', 'intranet_address_id' => 1, 'type' => 'invoice', 'number' => 1, 'message' => '', 'round_off' => '', 'total' => 100, 'payment_total' => 0, 'payment_online' => 0, 'girocode' => '', 'payment_method' => 2);
    function __construct()
    {
        $this->kernel = new Kernel;
        $this->kernel->setting = new FakeSetting();
        $this->kernel->intranet = new FakeIntranet;
        $this->contact = new FakeContact;
        $this->contact_person = new FakeContactPerson;
    }
    function get($key) {
        return $this->value[$key];
    }
    function getItems() {}
    function getIntranetAddress()
    {
        return new FakeAddress();
    }
    function getPaymentInformation()
    {
        return array('bank_name' => 'SparNord', 'bank_reg_number' => '1243', 'bank_account_number' => '12312345678', 'giro_account_number' => '112321321');
    }
    function getContactInformation()
    {
        return array('email' => 'test@intraface.dk', 'contact_name' => 'Lars Olesen');
    }

    function getInvoiceText()
    {
        return 'Ja, det kan du tro, at der er en masse at fortaelle.';
    }
}

class FakeTranslation
{
    function get($key) {
        switch ($key) {
            case 'invoice number':
                return 'Fakturanummer';
            case 'invoice due date':
                return 'Forfalden';
        }

    }
}

class DebtorPdfTest extends PHPUnit_Framework_TestCase
{
    function createPdf()
    {
        return new Debtor_Report_Pdf(new FakeTranslation);
    }

    function testConstruct()
    {
        $pdf = $this->createPdf();
        $this->assertTrue(is_object($pdf));
    }

    function testVisit()
    {
        error_reporting(E_ALL);
        $debtor = new FakeDebtor();
        $pdf = $this->createPdf();
        $pdf->visit($debtor);
        $pdf->output('file');
        $this->assertTrue(is_string($pdf->output('string')));
    }

}

?>