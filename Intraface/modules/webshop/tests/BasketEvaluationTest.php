<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/BasketEvaluation.php';
require_once 'Intraface/modules/webshop/Basket.php';

class FakeEvaluationIntranet {
    function get() {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeEvaluationUser {
    function hasModuleAccess() { return true; }
    function get() { return 1; }

}
class FakeEvaluationWebshop {
    public $kernel;
}


define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_INCLUDE_MODULE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/modules/');
define('PATH_INCLUDE_SHARED', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/shared/');
define('PATH_INCLUDE_CONFIG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/config/');


class BasketEvaluationTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->emptyEvaluationTable();
    }

    function tearDown()
    {
        $this->emptyEvaluationTable();
    }

    function emptyEvaluationTable()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE webshop_basket_evaluation');
        $db->query('TRUNCATE basket');
    }

    function createKernel()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeEvaluationIntranet;
        $kernel->user = new FakeEvaluationUser;
        return $kernel;
    }

    function createBasketEvaluation()
    {
        $kernel = $this->createKernel();
        return new BasketEvaluation($kernel);
    }

    function createBasket()
    {
        $kernel = $this->createKernel();
        $webshop = new FakeEvaluationWebshop();
        $webshop->kernel = $kernel;
        return new Basket($webshop, 'somesessionid');

    }

    function saveEvaluation()
    {

    }

    function testCreateBasketEvaluation()
    {
        $evaluation = $this->createBasketEvaluation();
        $this->assertTrue(is_object($evaluation));
    }

    function testSaveWithValidValuesSucceeds()
    {
        $evaluation = $this->createBasketEvaluation();

        $valid_data = array(
            'running_index' => 1,
            'evaluate_target_key' => 0,
            'evaluate_method_key' => 2,
            'evaluate_value' => 1000,
            'go_to_index_after' => 10000,
            'action_action_key' => 1,
            'action_value' => 10,
            'action_quantity' => 10,
            'action_unit_key' => 0
        );

        $this->assertTrue($evaluation->save($valid_data));
        $this->assertEquals(count($evaluation->getList()), 1);
    }

    function testDelete() {
        $evaluation = $this->createBasketEvaluation();

        $valid_data = array(
            'running_index' => 1,
            'evaluate_target_key' => 0,
            'evaluate_method_key' => 2,
            'evaluate_value' => 1000,
            'go_to_index_after' => 10000,
            'action_action_key' => 1,
            'action_value' => 10,
            'action_quantity' => 10,
            'action_unit_key' => 0
        );

        $this->assertTrue($evaluation->save($valid_data));
        $this->assertTrue($evaluation->delete());
        $this->assertEquals(count($evaluation->getList()), 0);
    }

    function testRun()
    {
        $evaluation = $this->createBasketEvaluation();

        $basket = $this->createBasket();

        // setting up products
        $basket->webshop->kernel->module('product');
        $product = new Product($basket->webshop->kernel);
        $product_id = $product->save(array('name' => 'Test', 'price' => 2000));

        $product = new Product($basket->webshop->kernel);
        $filter_product_id = $product->save(array('name' => 'Filterproduct', 'price' => -1));

        $valid_data = array(
            'running_index' => 1,
            'evaluate_target_key' => 0,
            'evaluate_method_key' => 2,
            'evaluate_value' => 1000,
            'go_to_index_after' => 10000,
            'action_action_key' => 1,
            'action_value' => $filter_product_id,
            'action_quantity' => 10,
            'action_unit_key' => 0
        );

        $this->assertTrue($evaluation->save($valid_data));

        $quantity = 2;
        $basket->add($product_id, $quantity);

        $this->assertTrue($evaluation->run($basket));

        print_r($basket->getItems());

    }
}
?>