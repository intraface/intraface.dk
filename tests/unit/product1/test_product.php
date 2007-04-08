<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';


if (!class_exists('MockSession')) {
	class MockSession extends Session {
		function start() {}
	}
}


class ProductTestCase extends UnitTestCase {

	private $kernel;

	function setUp() {
		$session = new MockSession();
		$this->kernel = new Kernel($session);
		$this->kernel->login('start@intraface.dk', 'startup');
		$this->kernel->isLoggedIn();
		$this->kernel->module('intranetmaintenance');

		// setting up the rights
		$module = new ModuleMaintenance($this->kernel);
		$intranet = new IntranetMaintenance($this->kernel, $this->kernel->intranet->get('id'));
		$intranet->setModuleAccess('product');
		$user = new UserMaintenance($this->kernel, $this->kernel->user->get('id'));
		$user->setModuleAccess('product', $this->kernel->intranet->get('id'));
		$this->kernel->module('product');

		$this->kernel->getModule('product');

	}

	function testProductCanGetNumberIfOtherProductDontNeedItAnymore() {
		$product = new Product($this->kernel);
		$number = $product->getMaxNumber() + 1;
		$new_number = $number + 1;
		if (!$product->save(array('number' => $number, 'name' => 'Test'))) {
			$product->error->view();
		}

		if (!$product->save(array('number' => $new_number, 'name' => 'Test'))) {
			$product->error->view();
		}

		$new_product = new Product($this->kernel);

		$array = array('number' => $number, 'name' => 'Test overtager nummer');
		if (!$new_product->save($array)) {
			$new_product->error->view();
		}

		$this->assertTrue($new_product->get('number') == $number);
	}


}
if (!isset($this)) {
	$test = new ProductTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}
?>