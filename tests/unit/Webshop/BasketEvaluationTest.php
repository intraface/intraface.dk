<?php
require_once 'Intraface/modules/webshop/BasketEvaluation.php';
require_once 'Intraface/modules/webshop/Basket.php';

class FakeEvaluationWebshop
{
    public $kernel;
}

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
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE webshop_basket_evaluation');
        $db->query('TRUNCATE basket');
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        return $kernel;
    }

    function createBasketEvaluation($id = 0)
    {
        $kernel = $this->createKernel();
        return new BasketEvaluation($kernel, $id);
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

    function testDelete()
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

        $basket->getItems(); // should have the filterproduct

    }
}
