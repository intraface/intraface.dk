<?php
require_once dirname(__FILE__) . './../config.local.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once PROJECT_PATH_ROOT . 'intraface.dk/config.local.php';
require_once PATH_INCLUDE . 'common.php';
require_once PATH_INCLUDE_MODULE . 'accounting/Account.php';

// det første vi skal gøre er at få flyttet alle redirects i
// filen så vi kan teste den - og når det er gjort kan vi
// så småt begynde at omskrive den til noget der er til at finde
// ud af.


class AccountingTestCase extends UnitTestCase {

	function testVatCalculation() {
		$this->assertTrue((80 + Account::calculateVat(100, 25)) == 100);
		$this->assertTrue((100 + Account::calculateVat(110, 10)) == 110);
		$this->assertTrue(round((93.40 + Account::calculateVat(100.41, 7.5)), 2) == 100.41);
	}

}


if (!isset($this)) {
	$test = new AccountingTestCase;
	if (TextReporter::inCli()) {
		exit($test->run(new TextReporter()) ? 0 : 1);
	}
	$test->run(new HtmlReporter());
}

?>