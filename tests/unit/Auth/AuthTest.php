<?php
session_start();
require_once dirname(__FILE__) . '/../config.test.php';

class FakeAuthObserver {}
class FakeAuthUser {
    function clearCachedPermission() { return true; }
    function getId() { return 1; }
}

class FakeAuthAdapter
{
    function auth()
    {
        $_SESSION['intraface_logged_in_user_id'] = 1;
        return new FakeAuthUser;
    }

    function getIdentification()
    {
        return 'fake user';
    }
}

class AuthTest extends PHPUnit_Framework_TestCase
{
    const SESSION_LOGIN = 'thissessionfirstlog';
	private $auth;
    private $db;
    protected $backupGlobals = FALSE;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);

        $this->auth = new Intraface_Auth(self::SESSION_LOGIN);
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE user');
        unset($this->auth);
    }

	function testConstructionOfAuth()
	{
	    $this->assertTrue(is_object($this->auth));
	}

	function testAuthMethodReturnsAnObjectOnSuccessFullAuthentication()
	{
	    $this->assertTrue(is_object($this->auth->authenticate(new FakeAuthAdapter)));
	}

	function testAfterAuthenticationTheIdentityCanBeGrappedUsingGetIdentity()
	{
		$db = MDB2::singleton();
	    $db->query('INSERT INTO user SET email="start@intraface.dk", session_id="'.self::SESSION_LOGIN.'"');
	    // @todo lidt dum da der skal v�re en Intraface_User tilg�ngelig.
	    $this->auth->authenticate(new FakeAuthAdapter);
		$identity = $this->auth->getIdentity($this->db);
	    $this->assertTrue(is_object($identity));
	}

	function createUserInDatabase()
    {
        // first we add a user.
        require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';
        $u = new UserMaintenance();
        $this->assertEquals(1, $u->update(array('email' => 'start@intraface.dk', 'password' => 'startup', 'confirm_password' => 'startup')));

    }
}
