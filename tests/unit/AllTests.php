<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

PHPUnit_Util_Filter::addDirectoryToWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/certificates/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/3Party/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/config/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/ihtml/'), '.php');
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/accounting/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/administration/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/contact/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/invoice/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/onlinepayment/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/order/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/procurement/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/product/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/todo/include_frontpage.php'));

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
                       'FileManager',
                       'Contact',
                       'Common',
                       'Accounting',
                       'CMS',
                       'Debtor',
                       'Shared',
                       'Newsletter',
                       'Keyword',
                       'Stock',
                       'OnlinePayment'
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