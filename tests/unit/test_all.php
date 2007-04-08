<?php
// der er et eller andet galt med den måde settings bliver inkluderet
// på, for test_all kan ikke virke.
require_once 'config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

$test = &new GroupTest('All intraface tests');
//$test->addTestFile(PATH_TEST . 'common/test_kernel.php');
//$test->addTestFile(PATH_TEST . 'accounting/test_account.php');
//$test->addTestFile(PATH_TEST . 'accounting/test_voucher.php');
$test->addTestFile(PATH_TEST . 'product/test_product.php');
//$test->addTestFile(PATH_TEST . 'xmlrpc/test_newsletter.php');
//$test->addTestFile(PATH_TEST . 'xmlrpc/test_webshop.php');

if (TextReporter::inCli()) {
	exit($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
?>