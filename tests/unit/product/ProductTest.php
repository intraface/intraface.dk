<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeProductIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeProductKernel {
    public $intranet;
    function useShared() {}
}

class ProductTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->kernel = new Kernel();
        $this->kernel->intranet = new FakeProductIntranet;
    }

    function testProductCanGetNumberIfOtherProductDontNeedItAnymore()
    {
        // TODO needs to be updated
        $this->markTestIncomplete('Find out how to have the detail load the unit stuff');

        $product = new Product($this->kernel);
        $number = $product->getMaxNumber() + 1;
        $new_number = $number + 1;
        if (!$product->save(array('number' => $number, 'name' => 'Test', 'price' => '20'))) {
            $product->error->view();
        }

        if (!$product->save(array('number' => $new_number, 'name' => 'Test', 'price' => 20))) {
            $product->error->view();
        }

        $new_product = new Product($this->kernel);

        $array = array('number' => $number, 'name' => 'Test overtager nummer', 'price' => 20);
        if (!$new_product->save($array)) {
            $new_product->error->view();
        }

        $this->assertTrue($new_product->get('number') == $number);
    }


}
?>