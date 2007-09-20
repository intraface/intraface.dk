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
        // TODO We need to figure out to test this without address blowing up
        $this->markTestIncomplete();
        $subscriber = $this->createSubscriber();
        $data = array('email' => 'test@legestue.net', 'ip' => 'ip');
        $this->assertTrue($subscriber->subscribe($data));
    }

    function testOptin()
    {
        // TODO We need to figure out to test this without address blowing up
        $this->markTestIncomplete('We need to figure out to test this without address blowing up');
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
}
?>