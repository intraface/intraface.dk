<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/User.php';
require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';


class UserTest extends PHPUnit_Framework_TestCase
{
    private $user;

    function setUp()
    {
        // @todo this has the notion with the standard database setup
        $this->user = new User(1);
        
        $m = new ModuleMaintenance();
        $m->register();
    }

    function tearDown()
    {
        $this->user = null;
    }

    ////////////////////////////////////////////////////////////////////////////

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
        // TODO and setup modules we can count on for the test
        $this->assertFalse($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('todo'));
        $this->user->setIntranetId(1); // spørgsmålet er om man bare skal have en init i stedet?
        $this->assertTrue($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('todo'));
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

    function testGetPermissionsReturnsAnArray()
    {
        $this->assertTrue(is_array($this->user->getPermissions()));
    }

    function testHasModuleAccessReturnsTrueOnAccess()
    {
        $this->user->setIntranetId(1);
        $this->assertTrue($this->user->hasModuleAccess('intranetmaintenance'));
    }

    function testHasModuleAccessReturnsFalseIfAccessIsNotGranted()
    {
        $this->user->setIntranetId(1);
        $this->assertFalse($this->user->hasModuleAccess('todo'));
    }

    function testClearCachedPermissionsEmptiesPermissionArrayAndSetsPermissionLoadedToFalse()
    {
        $this->user->setIntranetId(1);
        $this->user->clearCachedPermission();
        $this->assertEquals(0, count($this->user->getPermissions()));
        $this->assertFalse($this->user->permissionsLoaded());
    }
}
?>