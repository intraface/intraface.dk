<?php
require_once 'Intraface/XMLRPC/Newsletter/Server.php';

class NewsletterXMLRPCTest extends PHPUnit_Framework_TestCase
{
    protected $server;
    protected $db;

    function setUp()
    {
        $this->server = new Intraface_XMLRPC_Newsletter_Server;
        $this->db = MDB2::singleton(DB_DSN);
        $this->kernel = new Stub_Kernel;
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE contact');
        $this->db->exec('TRUNCATE newsletter_subscriber');
    }

    function testConstruction()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $this->assertTrue(is_object($this->server));
    }

    function testEmptyCredentialsThrowsException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $credentials = array();
        $list = 1;
        $email = 'test';

        try {
            $this->server->subscribe($credentials, $list, $email);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @group xmlrpc
     */
    function testIfANonExistingListIsUsedWithSubscribeAnExceptionIsThrown()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $client = $this->getClient();

        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');
        $list = 1; // non existing list
        $email = 'test';

        try {
            $client->subscribe($credentials, $list, $email);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @group xmlrpc
     */
    function testSubscribeHandlesStuff()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $client = $this->getClient();
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');
        $email = 'test';

        // create the list
        $newsletter = new NewsletterList($this->kernel);
        $data = array(
            'title' => 'title',
            'sender_name' => 'sender name',
            'reply_email' => 'reply@email.dk',
            'description' => 'description',
            'subscribe_message' => 'subscribe message',
            'subscribe_subject' => 'subscribe subject',
            'optin_link' => 'http://example.dk/'
            );
        $list = $newsletter->save($data);

        try {
            $client->subscribe($credentials, $list, $email);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    protected function getClient()
    {
        require_once dirname(__FILE__) . '/../../../install/Install.php';

        if (!defined('SERVER_STATUS')) {
            define('SERVER_STATUS', 'TEST');
        }
        $install = new Intraface_Install;
        $install->resetServer();
        $install->grantModuleAccess('administration', 'contact', 'newsletter');

        require_once 'XML/RPC2/Client.php';
        $options = array('prefix' => 'newsletter.', 'debug' => false);
        return XML_RPC2_Client::create(XMLRPC_SERVER_URL.'newsletter?version=0.2.0', $options);
    }
}
