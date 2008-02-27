<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/User.php';
require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';
require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';
require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
require_once 'Intraface/Validator.php';


class UserTest extends PHPUnit_Framework_TestCase
{
    private $user;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE user');
        $db->exec('TRUNCATE intranet');
        $db->exec('TRUNCATE modules');
        $db->exec('TRUNCATE permission');

        // @todo this has the notion with the standard database setup
        $u = new UserMaintenance();
        $u->update(array('email' => 'start@intraface.dk', 'password' => '123456', 'confirm_password' => '123456', 'disabled' => 0));

        $i = new IntranetMaintenance();
        $i->save(array('name' => 'intraface', 'identifier' => 'intraface'));

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
        $u = new UserMaintenance(1);
        $u->setIntranetAccess(1);

        $this->assertTrue($this->user->hasIntranetAccess(1));
        $this->assertFalse($this->user->hasIntranetAccess(2));
    }

    function testUserModuleAccessOnlyWorksWhenTheUserHasAnActiveIntranetId()
    {
        $u = new UserMaintenance(1);
        $u->setModuleAccess('intranetmaintenance', 1);

        // TODO how should we handle unknown modules
        // TODO and setup modules we can count on for the test
        $this->assertFalse($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('todo'));
        $this->user->setIntranetId(1); // spørgsmålet er om man bare skal have en init i stedet?
        $this->assertTrue($this->user->hasModuleAccess('intranetmaintenance'));
        $this->assertFalse($this->user->hasModuleAccess('todo'));
    }

    function testSubAccessReturnsFalseOnANotInitializedSubAccess()
    {
        $u = new UserMaintenance(1);
        $u->setIntranetAccess(1);
        $this->assertFalse($this->user->hasSubAccess(1, 1));
    }

    function testSetActiveIntranetReturnsAValueLargerThanZero()
    {
        $u = new UserMaintenance(1);
        $u->setModuleAccess('intranetmaintenance', 1);

        $this->assertTrue($this->user->setActiveIntranetId(1) > 0);
    }

    function testGetPermissionsReturnsAnArray()
    {
        $this->assertTrue(is_array($this->user->getPermissions()));
    }

    function testHasModuleAccessReturnsTrueOnAccess()
    {
        $u = new UserMaintenance(1);
        $u->setModuleAccess('intranetmaintenance', 1);

        $this->user->setIntranetId(1);
        $this->assertTrue($this->user->hasModuleAccess('intranetmaintenance'));
    }

    function testHasModuleAccessReturnsFalseIfAccessIsNotGranted()
    {
        $u = new UserMaintenance(1);
        $u->setIntranetAccess(1);
        $this->user->setIntranetId(1);
        $this->assertFalse($this->user->hasModuleAccess('todo'));
    }

    function testClearCachedPermissionsEmptiesPermissionArrayAndSetsPermissionLoadedToFalse()
    {
        $u = new UserMaintenance(1);
        $u->setIntranetAccess(1);

        $this->user->setIntranetId(1);
        $this->user->clearCachedPermission();
        $this->assertEquals(0, count($this->user->getPermissions()));
        $this->assertFalse($this->user->permissionsLoaded());
    }

    function testGetAddressReturnsObject()
    {
        $this->assertTrue(is_object($this->user->getAddress()));
    }

    function getActiveIntranetIdReturnsActiveId()
    {
        $u = new UserMaintenance(1);
        $u->setIntranetAccess(1);

        $this->user->setIntranetId(1);
        $this->assertEquals(1, $this->user->getActiveIntranetId());
    }
}