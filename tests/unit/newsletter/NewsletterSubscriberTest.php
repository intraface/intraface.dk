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
        return new NewsletterSubscriber($list);
    }

    function testSubscriber()
    {
        $subscriber = $this->createSubscriber();
        $this->assertTrue(is_object($subscriber));
    }

    function testAddObserver()
    {
        $subscriber = $this->createSubscriber();
        $subscriber->addObserver(new FakeObserver);
        $this->assertEquals(1, count($subscriber->getObservers()));
    }
}
?>