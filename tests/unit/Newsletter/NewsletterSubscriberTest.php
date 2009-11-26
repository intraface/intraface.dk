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
        $db->exec('TRUNCATE contact');
        $db->exec('TRUNCATE address');
    }

    function createSubscriber()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new Stub_Kernel;
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
        $mailer = new Stub_PhpMailer;
        $this->assertTrue($subscriber->subscribe($data, $mailer));
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
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
        $mailer = new Stub_PhpMailer;
        $this->assertTrue($subscriber->subscribe($data, $mailer));
        $this->assertTrue($mailer->isSend(), 'Mail is not send');
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

    function testGetContactReturnsContactObject()
    {
        $subscriber = new NewsletterSubscriber(new FakeNewsletterList);
        $contact = new FakeSubscriberContact;
        $subscriber->addContact($contact);
        $this->assertTrue(is_object($subscriber->getContact($contact->getId())));
    }

    function testDeleteSubscriber()
    {
        $subscriber = new NewsletterSubscriber(new FakeNewsletterList);
        $this->assertTrue($subscriber->delete());
    }

    function testGetListReturnsArray()
    {
        $subscriber = new NewsletterSubscriber(new FakeNewsletterList);
        $this->assertTrue(is_array($subscriber->getList()));
    }

    function testGetListReturnsActiveOptedInSubscribers()
    {
        $mailer = new Stub_PhpMailer;
        $subscriber = $this->createSubscriber();
        $subscriber->subscribe(array('name' => 'test1', 'email' => 'test1@intraface.dk', 'ip' => '0.0.0.0'), $mailer);

        $subscriber = $this->createSubscriber();
        $subscriber->subscribe(array('name' => 'test2', 'email' => 'test2@intraface.dk', 'ip' => '0.0.0.0'), $mailer);
        $subscriber->optin($subscriber->get('code'), '0.0.0.0');

        $subscriber = $this->createSubscriber();
        $subscriber->subscribe(array('name' => 'test3', 'email' => 'test3@intraface.dk', 'ip' => '0.0.0.0'), $mailer);
        $subscriber->optin($subscriber->get('code'), '0.0.0.0');
        $subscriber->delete();

        $list = $subscriber->getList();

        $this->assertEquals(1, count($list));
        $this->assertEquals('test2@intraface.dk', $list[0]['contact_email']);
    }
}

class FakeSubscriberContact
{
    function getId() { return 1000; }
}