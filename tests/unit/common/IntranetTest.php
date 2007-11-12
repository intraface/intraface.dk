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

    function testHasModuleAccess()
    {
        $this->assertTrue($this->intranet->hasModuleAccess('intranetmaintenance'));
        // @todo temporary hack with a module I know is registered and there is no access to
        $this->assertFalse($this->intranet->hasModuleAccess('todo'));
    }



    /*
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

    function testSetActiveIntranetReturnsAValueLargerThanZero()
    {
        $this->assertTrue($this->user->setActiveIntranetId(1) > 0);
    }
    */

}
?>