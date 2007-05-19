<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/modules/webshop/BasketEvaluation.php';

class FakeEvaluationKernel {
    public $intranet;
}
class FakeEvaluationIntranet {
    function get() {
        return 1;
    }
}
class FakeEvaluationWebshop {
    public $kernel;
}
class FakeEvaluationBasket {
    public $webshop;
}

define('DB_DSN', 'mysql://root:@localhost/pear');

class BasketEvaluationTest extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        $this->emptyEvaluationTable();
    }

    function emptyEvaluationTable()
    {
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE webshop_basket_evaluation');
    }

    function createKernel()
    {
        $kernel = new FakeEvaluationKernel;
        $kernel->intranet = new FakeEvaluationIntranet;
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
        $webshop = new FakeEvaluationWebshop($kernel);
        $basket = new FakeEvaluationBasket($webshop);
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
}
?>