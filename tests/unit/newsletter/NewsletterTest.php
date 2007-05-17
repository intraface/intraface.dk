<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Stubs.php';
require_once 'Intraface/modules/newsletter/Newsletter.php';

class TestableNewsletter extends Newsletter {
    public $value = array(
        'subject' => 'something',
        'body' => 'something',
        'deadline' => '2005-10-10 12:00:00',
        'id' => 1
    );

    function getContact() {
        return new FakeContact;
    }
    function getSubscribers() {
        for ($i = 0; $i<200;$i++) {
            $array[] = array(
                'contact_id' => '1',
                'contact_email' => 'test@email.dk'
            );
        }
        return $array;
    }

}

class NewsletterTest extends PHPUnit_Framework_TestCase {

    function testConstruction() {
        $list = new FakeNewsletterList();
        $newsletter = new Newsletter($list);
        $this->assertTrue(is_object($newsletter));
    }
    /*
    function testSubscribe() {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeKernel;
        $list->kernel->intranet = new FakeIntranet;
        $list->kernel->user = new FakeUser;
        $subscriber = new NewsletterSubscriber($list);
        $this->assertTrue($subscriber->subscribe(array('email' => 'test@legestue.net', 'ip' => 'ip')));
        $subscriber->subscribe(array('email' => 'test@legestue.net', 'ip' => 'ip'));
        echo $subscriber->error->view();
    }
    */

    function testQueue() {
        $list = new FakeNewsletterList();
        $list->kernel = new FakeKernel;
        $list->kernel->intranet = new FakeIntranet;
        $list->kernel->user = new FakeUser;
        $newsletter = new TestableNewsletter($list);
        $this->assertTrue($newsletter->queue());
    }
}
?>