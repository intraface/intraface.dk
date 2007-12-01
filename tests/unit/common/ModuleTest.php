<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Module.php';

class ModuleTest extends PHPUnit_Framework_TestCase
{
    function testUseModuleThrowsAnExceptionIfTheModuleIsNotValid()
    {
        try {
            Module::useModule('invalid module name');
            $this->assertTrue(false, 'Exception should have been thrown');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
}

