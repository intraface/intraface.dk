<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once '../SuperClass.php';

//require_once 'simpletest/unit_tester.php';
//require_once 'simpletest/reporter.php';
//require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE_MODULE . 'accounting/Year.php';

class YearTestCase extends SuperTestClass {

	function setUpChild() {
		/*
		$session = new MockSession();
		$this->kernel = new Kernel($session);
		$this->kernel->login('start@intraface.dk', 'startup');
		$this->kernel->isLoggedIn();
		*/
		$this->kernel->useModule('accounting');
	}

	function testSaveMethod() {
		$year = new Year($this->kernel);
		$this->assertFalse($year->get('id'));
		$this->assertTrue($year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
		$this->assertEqual('2000', $year->get('label'));
		$new_year = new Year($this->kernel, $year->get('id'), false);
		$this->assertTrue($new_year->save(array('label' => '2000 - edited', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
		$this->assertTrue($new_year->get('id') == $year->get('id'));
		$this->assertTrue($new_year->get('label') == '2000 - edited');

	}

}


if (!isset($this)) {
	$test = new YearTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}

?>