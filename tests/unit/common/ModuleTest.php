<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Module.php';

class ModuleTest extends PHPUnit_Framework_TestCase
{

    function testUseModule()
    {
        $this->markTestIncomplete('not finished yet');
        /*
        $this->expectError('module name invalid');
        Module::useModule('invalid module name');

        $this->assertTrue(Module::useModule('test'));
        */
    }

}
?>
