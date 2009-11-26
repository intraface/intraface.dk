<?php
require_once 'k.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

PHPUnit_Util_Filter::addDirectoryToWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/certificates/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/'), 'tpl.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/config/'), '.php');
PHPUnit_Util_Filter::removeDirectoryFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/ihtml/'), '.php');
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/accounting/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/administration/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/contact/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/invoice/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/onlinepayment/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/order/include_front.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/procurement/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/product/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/todo/include_frontpage.php'));
PHPUnit_Util_Filter::removeFileFromWhitelist(realpath(dirname(__FILE__) . '/../../src/Intraface/modules/modulepackage/include_front.php'));

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
                       'Language',
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