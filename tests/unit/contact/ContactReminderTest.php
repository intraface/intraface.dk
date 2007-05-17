<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/contact/ContactReminder.php';
require_once 'ContactStubs.php';

class ContactReminderTest extends PHPUnit_Framework_TestCase
{

    private $kernel;

    function setUp()
    {
    }

    function getContact()
    {
        $kernel = new FakeKernel;
        $kernel->intranet = new FakeIntranet;
        return new FakeContact($kernel);
    }

    function testConstruction()
    {
        $reminder = new ContactReminder($this->getContact());
        $this->assertTrue(is_object($reminder));
    }


}
?>