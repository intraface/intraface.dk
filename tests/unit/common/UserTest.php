<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/User.php';

class UserTest extends PHPUnit_Framework_TestCase
{
    private $user;

    function setUp()
    {
        // @todo this has the notion with the standard database setup
        $this->user = new User(1);
    }

    function testConstructionOfUser()
    {
        $this->assertTrue(is_object($this->user));
    }

    function testIntranetAccessReturnsTrueWhenTheUserHasIntranetAccessAndFalseIfNot()
    {
        $this->assertTrue($this->user->hasIntranetAccess(1));
        $this->assertFalse($this->user->hasIntranetAccess(2));
    }

    function testUserModuleAccessOnlyWorksWhenTheUserHasAnActiveIntranetId()
    {
        // TODO how should we handle unknown modules
        $this->assertFalse($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('cms'));
        $this->user->setIntranetId(1); // spørgsmålet er om man bare skal have en init i stedet?
        $this->assertTrue($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('cms'));
    }

    /*
    function testSubAccess()
    {
        // @todo This test should be completed
        $this->markTestIncomplete('This test should be completed');
    }
    */

    function testSetActiveIntranetReturnsAValueLargerThanZero()
    {
        $this->assertTrue($this->user->setActiveIntranetId(1) > 0);
    }


    function tearDown()
    {
        $this->user = null;
    }
}
?>