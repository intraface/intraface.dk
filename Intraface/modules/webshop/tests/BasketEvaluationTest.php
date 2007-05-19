<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/modules/webshop/BasketFilter.php';

class FakeKernel {
    public $intranet;
}
class FakeIntranet {
    function get() {
        return 1;
    }
}
class FakeWebshop {
    public $kernel;
}
class FakeBasket {
    public $webshop;
}


class BasketEvaluationTest extends PHPUnit_Framework_TestCase
{
    function createBasketEvaluation() {
        $kernel = new FakeKernel;
        $kernel->intranet = new FakeIntranet;

        $webshop = new FakeWebshop($kernel);

        $basket = new FakeBasket($webshop);

        return new WebshopFilter($basket);
    }

    function testCreateBasketEvaluation()
    {
        $evaluation = $this->createBasketEvaluation();
        $this->assertTrue(is_object($evaluation));
    }
}
?>