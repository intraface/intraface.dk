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
    }

    function getKernel()
    {
        $kernel = new FakeContactKernel;
        $kernel->intranet = new FakeContactIntranet;
        return $kernel;
    }

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
}
?>