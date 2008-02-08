<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

PHPUnit_Util_Filter::addDirectoryToWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/certificates/'), '.php');
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
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../Intraface/modules/modulepackage/include_front.php'));

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface');

        //'FileHandler',
        //'FileManager',

        $tests = array('Product',
                       'Email',
                       'Webshop',
                       'IntranetMaintenance',
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
            $suite->addTest(call_user_func(array($test.'_AllTests', 'suite')));
        }
        return $suite;
    }
}