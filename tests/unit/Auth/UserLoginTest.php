<?php
require_once dirname(__FILE__) . '/../config.test.php';

class UserLoginTest extends PHPUnit_Framework_TestCase 
{
    const SESSION_LOGIN = 'thissessionfirstlog';
	private $adapter;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE user');
        $db->exec('INSERT INTO user SET email = ' . $db->quote('start@intraface.dk', 'text') . ', password = ' . $db->quote(md5('startup'), 'text'));

        $this->adapter = new Intraface_Auth_User($db, self::SESSION_LOGIN, 'start@intraface.dk', 'startup');
    }

    function tearDown()
    {
		unset($this->adapter);
    }

	function testConstructionOfAdapter()
	{
	    $this->assertTrue(is_object($this->adapter));
	}
	
    function testAuthWithCorrectCredentials() 
    {
        $this->assertTrue(is_object($this->adapter->auth()));
    }	
}
