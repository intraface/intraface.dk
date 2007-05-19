<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class AllTests {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Tests');

        $tests = array('Product', 'Newsletter', 'Email', 'Intranetmaintenance', 'Filehandler', 'Contact', 'Common', 'Accounting', 'CMS');

        foreach ($tests AS $test) {
            require_once $test . '/All' . $test . 'Tests.php';
        }

        $suite->addTest(ProductTests::suite());
        $suite->addTest(NewsletterTests::suite());
        $suite->addTest(IntranetMaintenanceTests::suite());
        $suite->addTest(FilehandlerTests::suite());
        $suite->addTest(EmailTests::suite());
        $suite->addTest(ContactTests::suite());
        $suite->addTest(CommonTests::suite());
        $suite->addTest(CMSTests::suite());
        $suite->addTest(AccountingTests::suite());

        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
?>