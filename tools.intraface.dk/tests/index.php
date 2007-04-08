<?php
require_once 'configuration.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/web_tester.php';

require_once 'kundelogin/kundelogin.php';
require_once 'api/newsletter.php';
require_once 'api/webshop.php';

$test = &new GroupTest('All tests');
$test->addTestCase(new TestKundelogin());
//$test->addTestCase(new NewsletterTestCase());
//$test->addTestCase(new TestNewsletterAPI());
//$test->addTestCase(new WebshopTestCase());
//$test->addTestCase(new TestWebshopAPI());

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}

if (!$test->run(new HtmlReporter())) {
	mail('lsolesen@gmail.com', 'Fejl i kundelogin', 'Kig på testene for kundelogin.dk - der er en fejl');
}
?>