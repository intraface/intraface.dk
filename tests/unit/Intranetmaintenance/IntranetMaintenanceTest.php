<?php
require_once 'Intraface/Intranet.php';
require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';

class IntranetMaintenanceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE intranet');
        $db->exec('TRUNCATE user');
    }

    /////////////////////////////////////////77

    function testConstruct()
    {

        $i = new IntranetMaintenance();
        $this->assertEquals('IntranetMaintenance', get_class($i));

    }

    public function testSaveReturnsFalseOnInvalidInput()
    {
        $i = new IntranetMaintenance();
        $this->assertFalse($i->save(array()));
    }

    public function testSaveReturnsTrueOnValidInput()
    {
        $i = new IntranetMaintenance();
        $this->assertTrue($i->save(array('name' => 'Intraface.dk', 'identifier' => 'intraface')));
        $this->assertEquals('Intraface.dk', $i->get('name'));
        $this->assertEquals('intraface', $i->get('identifier'));
    }

    public function testSetMaintainedByUserId()
    {

        // first we need to set up af maintainer
        $i = new IntranetMaintenance();
        $i->save(array('name' => 'test', 'identifier' => 'test'));

        $u = new UserMaintenance();
        $u->update(array('email' => 'start@intraface.dk', 'password' => '123456', 'confirm_password' => '123456', 'disabled' => 0));
        $u->setIntranetAccess(1);

        $i = new IntranetMaintenance();
        $i->save(array('name' => 'intraface', 'identifier' => 'intraface'));
        $this->assertTrue($i->setMaintainedByUser(1, 1));
        $this->assertEquals(1, $i->get('maintained_by_user_id'));
    }
}
