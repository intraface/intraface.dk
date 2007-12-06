<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Auth.php';

class FakeAuthObserver {}

class AuthTest extends PHPUnit_Framework_TestCase {

    const SESSION_LOGIN = 'thissessionfirstlog';

    function setUp() {
        
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE user');
        
        $auth = new Auth(self::SESSION_LOGIN);
        if ($auth->isLoggedIn()) {
            $auth->logout();
        }
    }
    
    function tearDown() {
        $auth = new Auth(self::SESSION_LOGIN);
        if ($auth->isLoggedIn()) {
            $auth->logout();
        }
    }
    
    function createUserInDatabase() {
        // first we add a user.
        require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';
        $u = new UserMaintenance();
        $this->assertEquals(1, $u->update(array('email' => 'start@intraface.dk', 'password' => 'startup', 'confirm_password' => 'startup')));
        
    }
    
    function createLoggedInAuth() {
        $this->createUserInDatabase();
        $auth = new Auth(self::SESSION_LOGIN);
        $auth->login('start@intraface.dk', 'startup');
        $auth->isLoggedIn();
        return $auth;
    }
    
    ////////////////////////////////////////////////

    function testConstructionOfAuth() {
        $auth = new Auth(self::SESSION_LOGIN);
        $this->assertTrue(is_object($auth));
    }

    function testLoginFailsOnIncorrectCredentials() {
        $this->createUserInDatabase();
        $auth = new Auth(self::SESSION_LOGIN);
        $this->assertFalse($auth->login('incorrect@email.dk', 'incorrectpass'));
        $this->assertFalse($auth->isLoggedIn());
        $this->assertFalse($auth->login('incorrect@email.dk', 'startup'));
        $this->assertFalse($auth->isLoggedIn());
        $this->assertFalse($auth->login('start@intraface.dk', 'incorrectpass'));
        $this->assertFalse($auth->isLoggedIn());

    }

    function testLoginSucceedsOnCorrectCredentials() {
        $this->createUserInDatabase();
        $auth = new Auth(self::SESSION_LOGIN);
        $this->assertTrue($auth->login('start@intraface.dk', 'startup'));
        $this->assertTrue(($auth->isLoggedIn() > 0));
    }

    function testLogout() {
        $auth = $this->createLoggedInAuth();
        $this->assertTrue($auth->logout());
        $this->assertFalse($auth->isLoggedIn());
    }


    function testChangeOfSessionIsNotLoggedIn() {
        $auth = $this->createLoggedInAuth();
        $auth = new Auth('anotherdifferntsession');
        $this->assertFalse($auth->isLoggedIn());
    }

    
    function testAttach() {
        $auth = new Auth('session');
        $auth->attachObserver(new FakeAuthObserver);
        $observers = $auth->getObservers();
        $this->assertTrue(count($observers) == 1);
        // this assert is a
    }

    
}
?>