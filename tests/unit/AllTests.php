<?php
require_once 'k.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Testsuite');

        $tests = array('Product',
                       'Email',
                       'Webshop',
                       'Shop',
                       'Intranetmaintenance',
                       'Contact',
                       'Common',
                       'Accounting',
                       'CMS',
                       'Debtor',
                       'Shared',
                       'Newsletter',
                       'Filehandler',
        			   'Filemanager',
                       'Keyword',
                       'Stock',
                       'Onlinepayment',
                       'Procurement',
                       'Project',
                       'XMLRPC',
                       'Auth',
                       'Currency'
        );

        foreach ($tests as $test) {
            require_once $test . '/AllTests.php';
            $suite->addTest(call_user_func(array($test.'_AllTests', 'suite')));
        }
        return $suite;
    }
}