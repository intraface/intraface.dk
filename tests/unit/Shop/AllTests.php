<?php
require_once 'PHPUnit/TextUI/TestRunner.php';

class Shop_AllTests
{
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Shop');

        $tests = array(
            'ShopBasket',
            'ShopBasketEvaluation',
            'Shop',
            'ShopFeaturedProducts'
        );

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
