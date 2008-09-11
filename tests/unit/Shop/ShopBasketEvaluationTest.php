<?php
require_once dirname(__FILE__) . '/../config.test.php';

class FakeShopEvaluationIntranet {
    function getId() {
        return 1;
    }
    function get()
    {
        return 1;
    }
    function hasModuleAccess() {
        return true;
    }
}

class FakeShopEvaluationCoordinator
{
    public $kernel;
    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }
}

class FakeShopEvaluationShop
{
    function getId()
    {
        return 1;
    }
}

class FakeShopEvaluationUser {
    function hasModuleAccess() { return true; }
    function get() { return 1; }
    function getActiveIntranetId() { return 1; }
}
class FakeShopEvaluationWebshop {
    public $kernel;
}

class ShopBasketEvaluationTest extends PHPUnit_Framework_TestCase
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
        $kernel = new Intraface_Kernel;
        $kernel->intranet = new FakeShopEvaluationIntranet;
        $kernel->user = new FakeShopEvaluationUser;
        return $kernel;
    }

    function createBasketEvaluation($id = 0)
    {
        return new Intraface_modules_shop_BasketEvaluation(MDB2::singleton(DB_DSN), new FakeShopEvaluationIntranet, new FakeShopEvaluationShop, $id);
    }

    function createBasket()
    {
        $kernel = $this->createKernel();
        $webshop = new FakeShopEvaluationWebshop();
        $webshop->kernel = $kernel;
        
        return new Intraface_modules_shop_Basket(MDB2::singleton(DB_DSN), 
            new FakeShopEvaluationIntranet, 
            new FakeShopEvaluationCoordinator($this->createKernel()),
            new FakeShopEvaluationShop, 'some session');
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

    function testItIsPossibleToLoadAnEvaluationAgainAndItIsLoadedAutomatically()
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

        $evaluation->save($valid_data);
        $id = $evaluation->getId();

        $evaluation = $this->createBasketEvaluation($id);
        $this->assertEquals('price', $evaluation->get('evaluate_target'));
        $this->assertEquals('at_least', $evaluation->get('evaluate_method'));
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
        //$basket->webshop->kernel->module('product');
        $product = new Product($this->createKernel());
        $product_id = $product->save(array('name' => 'Test', 'price' => 2000));

        $product = new Product($this->createKernel());
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

        $basket->getItems(); // should have the filterproduct

    }
}
?>