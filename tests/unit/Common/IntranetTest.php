<?php
require_once 'Intraface/Intranet.php';
require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';

class IntranetTest extends PHPUnit_Framework_TestCase
{
    private $intranet;

    function setUp()
    {
        // @todo this has the notion with the standard database setup
        //       and better setting of moduleaccess

        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE intranet');
        $db->exec('TRUNCATE modules');

        $i = new IntranetMaintenance();
        $i->save(array('name' => 'intraface', 'identifier' => 'intraface'));

        $m = new ModuleMaintenance();
        $m->register();

        $this->intranet = new Intraface_Intranet(1);

    }

    function tearDown()
    {
        $this->intranet = null;
    }

    ////////////////////////////////////////////////////////////////////////////

    function testConstructionOfIntranet()
    {
        $this->assertTrue(is_object($this->intranet));
    }

    function testHasModuleAccessReturnsTrueWhenIntranetHasAccess()
    {
        $i = new IntranetMaintenance(1);
        $i->setModuleAccess('intranetmaintenance');

        $this->assertTrue($this->intranet->hasModuleAccess('intranetmaintenance'));
    }

    function testHasModuleAccessReturnsFalseWhenIntranetDoesNotHaveAccess()
    {
        // @todo temporary hack with a module I know is registered and there is no access to
        $this->assertFalse($this->intranet->hasModuleAccess('todo'));
    }

    function hasModuleAccessThrowsExceptionOnAnInvalidModule()
    {
        try {
            $this->intranet->hasModuleAccess('nevervalid');
            $this->assertTrue(false, 'Exception should have been thrown');
        } catch (Exception $e) {
            $this->assertTrue(true, 'Exception was correctly thrown');
        }
    }
}
