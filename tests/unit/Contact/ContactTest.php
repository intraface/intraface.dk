<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/contact/Contact.php';
require_once 'ContactStubs.php';

class ContactTest extends PHPUnit_Framework_TestCase
{

    private $kernel;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE address');
        $db->query('TRUNCATE contact');
    }

    function getKernel()
    {
        $kernel = new Stub_Kernel;
        return $kernel;
    }

    /////////////////////////////////////////////////////////

    function testConstruction()
    {
        $contact = new Contact($this->getKernel());
        $this->assertTrue(is_object($contact));
    }

    function testNeedOptin()
    {
        $contact = new Contact($this->getKernel(), 7);
        $array = $contact->needNewsletterOptin();
        $this->assertTrue(is_array($array));
    }

    function testSave()
    {
        $contact = new Contact($this->getKernel(), 7);
        $data = array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269');
        $this->assertTrue($contact->save($data) > 0);
    }

    function testGetSimilarContacts()
    {
        $contact = new Contact($this->getKernel());
        $data = array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269');
        $contact->save($data);

        $contact = new Contact($this->getKernel());
        $data = array('name' => 'Tester 1', 'email' => 'lars@legestue.net', 'phone' => '26176860');
        $contact->save($data);

        $this->assertTrue($contact->hasSimilarContacts());

        $similar_contacts = $contact->getSimilarContacts();

        $this->assertEquals(1, count($similar_contacts));
    }
}
