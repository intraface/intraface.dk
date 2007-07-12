<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../config.test.php';


require_once 'Intraface/XMLRPC/Contact/Server.php';

class FakeContactServerKernel {
    public $intranet;
    public $setting;
}

class FakeContactServerSetting {
    function get() {
        return '';
    }
}
class FakeContactServerIntranet {
    private $id;
    function __construct($id) {
        $this->id = $id;
    }
    function get() {
        return $this->id;
    }
}

class ContactServerTest extends PHPUnit_Framework_TestCase {

    protected $client;
    protected $credentials;
    protected $methods;
    private $kernel;
    private $private_key;
    private $contact_key;
    private $insert_id;

    function __construct() {
        $this->private_key = md5('private' . date('d-m-Y H:i:s') . 'test');
        $this->public_key = md5('public' . date('d-m-Y H:i:s') . 'test');
        $this->contact_key = md5('contact_key' . date('Y-m-d H:i:s') . 'test');
    }

    function setUp()
    {

    }

    function testTestIncomplete()
    {
        // TODO needs to be updated        
        $this->markTestIncomplete('not finished yet');

    }

    /*
    function createKernel($intranet_id)
    {
        $this->kernel = new FakeContactServerKernel;
        $this->kernel->intranet = new FakeContactServerIntranet($intranet_id);
        $this->kernel->setting = new FakeContactServerSetting;

    }

    function createIntranetCredentials()
    {
        $db = MDB2::factory(DB_DSN);
        $db->exec('INSERT INTO intranet SET private_key = ' . $db->quote($this->private_key, 'text') . ', public_key = ' . $db->quote($this->public_key, 'text'));
        return $db->lastInsertId();
    }

    function createContactCredentials()
    {
        $intranet_id = $this->createIntranetCredentials();

        $db = MDB2::factory(DB_DSN);
        $result = $db->exec('INSERT INTO contact
            SET
                intranet_id = '.$db->quote($intranet_id, 'integer').',
                password = ' . $db->quote($this->contact_key, 'text'));
        return $db->lastInsertId();
    }


    function testCheckCredentialsWithoutKernel() {
        $credentials = array(
            'private_key' => $this->private_key,
            'session_id' => 'somesessionid'
        );
        $server = new Intraface_XMLRPC_Contact;
        $this->assertTrue($server->checkCredentials($credentials));
    }

    function testAuthenticateContact() {
        $credentials = array(
            'private_key' => $this->private_key,
            'session_id' => 'somesessionid'
        );

        $server = new Intraface_XMLRPC_Contact();
        $this->assertTrue($server->authenticateContact($credentials, $this->contact_key));

    }

    function testGetContact() {
        $credentials = array(
            'private_key' => $this->private_key,
            'session_id' => 'somesessionid'
        );
        $server = new Intraface_XMLRPC_Contact();
        $this->assertTrue($server->getContact($credentials, $this->insert_id));
    }

    function testSaveContact() {
        $credentials = array(
            'private_key' => $this->private_key,
            'session_id' => 'somesessionid'
        );
        $server = new Intraface_XMLRPC_Contact;
        $data = array(
            'name' => 'name'
        );
        $this->assertTrue($server->saveContact($credentials, $data));
    }
    */
}
?>