<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/User.php';

class UserTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }

    function testConstructionOfUser()
    {
        $user = new User(1);
        $this->assertTrue(is_object($user));
    }

    function testIntranetAccess()
    {
        $user = new User(1);
        $this->assertTrue($user->hasIntranetAccess(1));
        $this->assertFalse($user->hasIntranetAccess(2));
    }

    function testUserModuleAccess()
    {
        // TODO how should we handle unknown modules
        $user = new User(1);
        $this->assertFalse($user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($user->hasModuleAccess('cms'));
        $user->setIntranetId(1); // spørgsmålet er om man bare skal have en init i stedet?
        $this->assertTrue($user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($user->hasModuleAccess('cms'));
    }

    function testSetActiveIntranet()
    {
        $user = new User(1);
        $this->assertTrue($user->setActiveIntranetId(1) > 0);
    }


    function tearDown()
    {
    }
}
?>