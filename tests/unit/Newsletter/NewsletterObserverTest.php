<?php
require_once 'Intraface/modules/newsletter/Observer/OptinMail.php';
require_once 'NewsletterStubs.php';

class FakeNewsletterMailer {

}

class NewsletterObserverTest extends PHPUnit_Framework_TestCase
{
    function createObserver()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new Stub_Kernel;
        return new Intraface_Module_Newsletter_Observer_OptinMail($list, new FakeNewsletterMailer);
    }

    ////////////////////////////////////////////////////////////////////

    function testCreateObserver()
    {
        $observer = $this->createObserver();
        $this->assertTrue(is_object($observer));
    }
}
