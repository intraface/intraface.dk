<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/Module.php';

class ModuleTestCase extends UnitTestCase {

	function testUseModule() {

		$this->expectError('module name invalid');
		Module::useModule('invalid module name');

		$this->assertTrue(Module::useModule('test'));


	}

}

if (!isset($this)) {
	$test = new ModuleTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>
