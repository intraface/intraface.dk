<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'NewsletterStubs.php';
require_once 'Intraface/modules/newsletter/Newsletter.php';

class TestableNewsletter extends Newsletter
{
    public $value = array(
        'subject' => 'something',
        'body' => 'something',
        'deadline' => '2005-10-10 12:00:00',
        'id' => 1
    );

    function getContact()
    {
        return new FakeNewsletterContact;
    }

    function getSubscribers()
    {
        for ($i = 0; $i<10000;$i++) {
            $array[] = array(
                'contact_id' => '1',
                'contact_email' => 'test@email.dk'
            );
        }
        return $array;
    }
}

class NewsletterTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE newsletter_archieve');
    }

    function createEmptyNewsletter()
    {
        $list = new FakeNewsletterList();
        return new Newsletter($list);
    }

    function testConstruction()
    {
        $list = new FakeNewsletterList();
        $newsletter = new Newsletter($list);
        $this->assertTrue(is_object($newsletter));
    }

    function testSaveReturnsAnIntegerWhichCorrespondsTheIdOfTheNewsletterAndActuallyPersistsANewsletter()
    {
        $newsletter = $this->createEmptyNewsletter();
        $subject = 'test';
        $data = array(
            'subject' => $subject,
            'text' => 'test',
            'deadline' => date('Y-m-d')
        );
        $this->assertTrue($newsletter->save($data) > 0);
        $list = $newsletter->getList();
        $this->assertEquals($subject, $list[0]['subject']);
    }

    function testQueuingOf10000Subscribers()
    {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeNewsletterKernel;
        $list->kernel->intranet = new FakeNewsletterIntranet;
        $list->kernel->user = new FakeNewsletterUser;
        $newsletter = new TestableNewsletter($list);
        $this->assertTrue($newsletter->queue());
    }

    function testDelete()
    {
        $newsletter = $this->createEmptyNewsletter();
        $data = array(
            'subject' => 'test',
            'text' => 'test',
            'deadline' => date('Y-m-d')
        );
        $newsletter->save($data);
        $this->assertTrue($newsletter->delete());
        $this->assertEquals(0, count($newsletter->getList()));

    }
}
?>