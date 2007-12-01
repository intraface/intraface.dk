<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Intranet.php';

class IntranetTest extends PHPUnit_Framework_TestCase
{
    private $intranet;

    function setUp()
    {
        // @todo this has the notion with the standard database setup
        //       and better setting of moduleaccess
        $this->intranet = new Intranet(1);
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
        }
        catch (Exception $e) {
            $this->assertTrue(true, 'Exception was correctly thrown');
        }
    }
}