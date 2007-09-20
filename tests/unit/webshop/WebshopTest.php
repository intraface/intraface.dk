<?php
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/modules/webshop/Basket.php';
require_once 'Intraface/modules/product/ProductDetail.php';

define('DB_DSN', 'mysql://root:@localhost/pear');
define('PATH_INCLUDE_MODULE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/modules/');
define('PATH_INCLUDE_SHARED', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/shared/');
define('PATH_INCLUDE_CONFIG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/config/');

class WebshopTest extends PHPUnit_Framework_TestCase
{
    function testPlaceOrderResetsBasket()
    {
        // TODO needs to be updated
        $this->markTestIncomplete('Needs a test for this');
    }


}
?>