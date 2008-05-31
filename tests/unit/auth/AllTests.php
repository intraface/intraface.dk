<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class Auth_AllTests {

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Auth_Tests');

        $tests = array('Auth', 'PrivateKeyLogin', 'PublicKeyLogin', 'UserLogin', 'Weblogin');

        foreach ($tests AS $test) {
            require_once $test . 'Test.php';
            $suite->addTestSuite($test . 'Test');
        }

        return $suite;
    }
}
?>