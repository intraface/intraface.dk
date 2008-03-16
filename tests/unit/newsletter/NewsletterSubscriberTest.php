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
    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE newsletter_subscriber');
        $db->exec('TRUNCATE newsletter_archieve');
    }

    function createSubscriber()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeNewsletterKernel;
        $list->kernel->intranet = new FakeNewsletterIntranet;
        return new NewsletterSubscriber($list);
    }

    function testConstructionSubscriber()
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

    function testUnSubscribe()
    {
        $subscriber = $this->createSubscriber();
        $this->assertTrue($subscriber->unsubscribe('test@legestue.net'));
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
        $this->assertTrue($subscriber->optedIn());
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