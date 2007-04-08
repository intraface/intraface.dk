<?php
require_once dirname(__FILE__) . '/../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE . 'common/core/Module.php';

class ModuleTestCase extends UnitTestCase {

	function testUseModule() {

		$this->expectError('module name invalid');
		Module::useModule('invalid module name');

		$this->assertTrue(Module::useModule('test'));


	}

}

$test = &new ModuleTestCase();
$test->run(new HtmlReporter());
?>
