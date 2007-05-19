<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeProductIntranet {
    function get() {
        return 1;
    }
}

class FakeProductKernel {
    public $intranet;
}

class ProductTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->kernel = new FakeProductKernel();
        $this->kernel->intranet = new FakeProductIntranet;
    }

    function testProductCanGetNumberIfOtherProductDontNeedItAnymore() {

        $this->markTestIncomplete('product needs to relaxe a bit');

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
?>