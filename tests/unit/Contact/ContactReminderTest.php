<?php
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
        $kernel = new FakeContactKernel;
        $kernel->intranet = new FakeContactIntranet;
        return new FakeContactContact($kernel);
    }

    function testConstruction()
    {
        $reminder = new ContactReminder($this->getContact());
        $this->assertTrue(is_object($reminder));
    }

    function testPostPhoneUntil()
    {
        $reminder = new ContactReminder($this->getContact());
        $this->assertTrue($reminder->postponeUntil(date('Y-') . date('m') + 1 . date('-d')));
    }


}
