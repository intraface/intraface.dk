<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'NewsletterStubs.php';
require_once 'Intraface/modules/newsletter/NewsletterList.php';

class NewsletterListTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE newsletter_list');
    }

    function createEmptyList()
    {
        return new Newsletterlist(new Stub_Kernel);
    }

    function testConstruction()
    {
        $list = $this->createEmptyList();
        $this->assertTrue(is_object($list));
    }

    function testSaveReturnsInteger()
    {
        $list = $this->createEmptyList();
        $data = array(
            'title' => 'title',
            'sender_name' => 'sender name',
            'reply_email' => 'reply@email.dk',
            'description' => 'description',
            'subscribe_message' => 'subscribe message',
            'subscribe_subject' => 'subscribe subject',
            'optin_link' => 'http://example.dk/'
            );

        $this->assertTrue($list->save($data) > 0);
        $list = $list->getList();
        $this->assertEquals('title', $list[0]['title']);
    }

    function testDeleteReturnsTrue()
    {
        $list = $this->createEmptyList();
        $data = array(
            'title' => 'title',
            'sender_name' => 'sender name',
            'reply_email' => 'reply@email.dk',
            'description' => 'description',
            'subscribe_subject' => 'subscribe subject',
            'subscribe_message' => 'subscribe message',
            'optin_link' => 'http://example.dk/');
        $this->assertTrue($list->save($data) > 0);
        $this->assertTrue($list->delete());
        $this->assertEquals(0, count($list->getList()));
    }

}