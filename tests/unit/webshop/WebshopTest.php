<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';

class FakeBasketKernel
{
    public $intranet;
    public $user;
    function useModule()
    {
        return true;
    }
    function useShared()
    {
        return true;
    }
}
class FakeBasketIntranet
{
    function get()
    {
        return 1;
    }
    function hasModuleAccess()
    {
        return true;
    }
}

class FakeBasketUser
{
    function hasModuleAccess()
    {
        return true;
    }
    function get()
    {
        return 1;
    }


} // used for DBQuery

class FakeBasketWebshop
{
    public $kernel;
}


define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_INCLUDE_MODULE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/modules/');
define('PATH_INCLUDE_SHARED', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/shared/');
define('PATH_INCLUDE_CONFIG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/config/');

class BasketTest extends PHPUnit_Framework_TestCase
{

    function testPlaceOrderResetsBasket()
    {
        $this->markTestIncomplete('Needs a test for this');
    }


}
?>