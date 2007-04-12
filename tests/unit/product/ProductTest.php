<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeIntranet {
	function get() {
		return 1;
	}
}

class FakeKernel {
	public $intranet;
}

class ProductTestCase extends UnitTestCase {

	function setUp() {
		$this->kernel = new FakeKernel();
		$this->kernel->intranet = new FakeIntranet;
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