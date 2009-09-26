<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/XMLRPC/Newsletter/Server.php';

class NewsletterXMLRPCServerIntranet
{
    function get()
    {
        return 1;
    }
}

class NewsletterXMLRPCServerKernel
{
    public $intranet;
    function __construct()
    {
        $this->intranet = new NewsletterXMLRPCServerIntranet;
    }
}

class NewsletterXMLRPCTest extends PHPUnit_Framework_TestCase
{
    private $server;

    function setUp()
    {
        $this->server = new Intraface_XMLRPC_Newsletter_Server;
        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE contact');
        $db->exec('TRUNCATE newsletter_subscriber');
        $this->kernel = new NewsletterXMLRPCServerKernel;

    }

    function tearDown()
    {

    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->server));
    }

    function testEmptyCredentialsThrowsException()
    {
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

    function testIfANonExistingListIsUsedWithSubscribeAnExceptionIsThrown()
    {
        $client = $this->getClient();

        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');
        $list = 1;
        $email = 'test';

        try {
            $client->subscribe($credentials, $list, $email);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }

    function testSubscribeHandlesStuff()
    {
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

    function getClient()
    {
        require_once dirname(__FILE__) . '/../../../install/Install.php';

        if (!defined('SERVER_STATUS')) {
            define('SERVER_STATUS', 'TEST');
        }
        $install = new Intraface_Install;
        $install->resetServer();
        $install->grantModuleAccess('administration', 'contact', 'newsletter');

        require_once 'XML/RPC2/Client.php';
        $options = array('prefix' => 'newsletter.');
        return XML_RPC2_Client::create(XMLRPC_SERVER_URL.'newsletter/', $options);
    }


    /*
    function testInvalidKeyThrowsException()
    {
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');
        $data = array();
        try {
            $this->server->subscribe($credentials, $data);
            $this->assertFalse(true, 'Should have thrown an exception');
        } catch (XML_RPC2_FaultException $e) {
            $this->assertTrue(true);
        }
    }


    function testGetContactWithDanishCharactersWorks()
    {
        $client = $this->getClient();
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');

        $contact = new Contact(new ContactXMLRPCServerKernel);
        $data = array('name' => 'Tester זרו');
        $contact->save($data);

        $this->assertEquals('Tester זרו', $client->getContact($credentials, $contact->getId()));

    }

    function testSaveContactWorksWithDanishCharacters()
    {
        $client = $this->getClient();
        $credentials = array('private_key' => 'privatekeyshouldbereplaced', 'session_id' => 'something');

        $contact = new Contact(new ContactXMLRPCServerKernel);
        $data = array('name' => 'Tester');
        $contact->save($data);

        $new_name = 'Tester aaa';
        $data = array('id' => $contact->getId(), 'name' => $new_name);
        $this->assertTrue($client->saveContact($credentials, $data));

        $saved_contact = new Contact(new ContactXMLRPCServerKernel, $contact->getId());
        $this->assertEquals($new_name, $saved_contact->get('name'));

    }
*/
}
