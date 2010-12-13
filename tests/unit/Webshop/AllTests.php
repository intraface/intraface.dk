<?php
require_once 'PHPUnit/TextUI/TestRunner.php';

class Webshop_AllTests
{
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Webshop');

        $tests = array('Basket', 'BasketEvaluation', 'Webshop', 'FeaturedProducts');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
