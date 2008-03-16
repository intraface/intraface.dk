<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/newsletter/NewsletterSubscriber.php';
require_once 'NewsletterStubs.php';

class FakeObserver
{
    function update() {}
}

class NewsletterSubscriberTest extends PHPUnit_Framework_TestCase
{

    function createSubscriber()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeNewsletterKernel;
        $list->kernel->intranet = new FakeNewsletterIntranet;
        return new NewsletterSubscriber($list);
    }

    function testSubscriber()
    {
        $subscriber = $this->createSubscriber();
        $this->assertTrue(is_object($subscriber));
    }

    function testSubscribe()
    {
        $subscriber = $this->createSubscriber();
        $data = array('email' => 'test@legestue.net', 'ip' => 'ip');
        $this->assertTrue($subscriber->subscribe($data));
    }

    function testOptin()
    {
        $subscriber = $this->createSubscriber();
        $data = array('email' => 'test@legestue.net', 'ip' => 'ip');
        $this->assertTrue($subscriber->subscribe($data));

        $code = 'wrongcode';
        $ip = 'ip';

        $this->assertFalse($subscriber->optIn($code, $ip));
        $code = $subscriber->get('code');
        $this->assertTrue($subscriber->optIn($code, $ip));
    }

    function testAddObserver()
    {
        $subscriber = new NewsletterSubscriber(new FakeNewsletterList);
        $subscriber->addObserver(new FakeObserver);
        $this->assertEquals(1, count($subscriber->getObservers()));
    }

    function testAddContactReturnsInteger()
    {
        $subscriber = new NewsletterSubscriber(new FakeNewsletterList);
        $this->assertTrue($subscriber->addContact(new FakeSubscriberContact) > 0);
    }
}

class FakeSubscriberContact
{
    function getId() { return 1000; }
}