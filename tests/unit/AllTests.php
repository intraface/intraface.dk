<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface');

        $tests = array('Product',
                       'Email',
                       'Webshop',
                       'IntranetMaintenance',
                       'FileHandler',
                       'Contact',
                       'Common',
                       'Accounting',
                       'CMS',
                       'Debtor',
                       'Shared',
                       'Newsletter',
                       'Keyword',
                       'Stock'
        );

        foreach ($tests AS $test) {
            require_once strtolower($test) . '/AllTests.php';
        }

        $suite->addTest(Accounting_AllTests::suite());
        $suite->addTest(CMS_AllTests::suite());
        $suite->addTest(Common_AllTests::suite());
        $suite->addTest(Contact_AllTests::suite());
        $suite->addTest(Email_AllTests::suite());
        $suite->addTest(Filehandler_AllTests::suite());
        $suite->addTest(IntranetMaintenance_AllTests::suite());
        $suite->addTest(Newsletter_AllTests::suite());
        $suite->addTest(Product_AllTests::suite());
        $suite->addTest(Webshop_AllTests::suite());
        $suite->addTest(Debtor_AllTests::suite());
        $suite->addTest(Shared_AllTests::suite());
        $suite->addTest(Keyword_AllTests::suite());
        $suite->addTest(Stock_AllTests::suite());
        return $suite;
    }
}
?>