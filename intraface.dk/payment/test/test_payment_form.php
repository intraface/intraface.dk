<?php
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';

class TestPaymentForm extends WebTestCase {

	private $url;

	function __construct($url) {
		$this->url = $url;
	}

	function testSSL() {

	}

    function testFormFieldsPresent() {
		$this->get($this->url);
		$this->assertField('cardnumber', '');
		$this->assertField('cvd', '');
		$this->assertField('expirationdate_year', '');
		$this->assertField('expirationdate_month', '');
    }

    function testFormNotFilledOutWhenSubmitting() {
    	$this->get($this->url);
    	$this->assertTrue($this->clickSubmit('Send'));
    	$this->assertText('error');
    }

    function testWrongCardNumberSubmitted() {
    	$this->get($this->url);
    	$this->assertTrue($this->setField('cardnumber', 'xxx'));
    	$this->assertTrue($this->setField('cvd', 'xxx'));
    	$this->assertTrue($this->setField('expirationdate_year', 'xxx'));
    	$this->assertTrue($this->setField('expirationdate_month', 'xxx'));
    	$this->assertTrue($this->clickSubmit('Send'));
    	$this->assertText('error');
    }

    function testCorrectCardNumberSubmitted() {
    	$this->get($this->url);
    	$this->assertTrue($this->setField('cardnumber', '4571900400601458'));
    	$this->assertTrue($this->setField('cvd', '643'));
    	$this->assertTrue($this->setField('expirationdate_year', '12'));
    	$this->assertTrue($this->setField('expirationdate_month', '07'));
    	$this->assertTrue($this->clickSubmit('Send'));
    	$this->assertNoText('curl'); // curl has to be installed
    	$this->assertNoText('Communication Error'); // curl has not been installed properly
    	$this->assertText('Authorization');
    }
}
$test = new TestPaymentForm('http://localhost/Intraface/intraface.dk/payment/index.php');
$test->run(new HtmlReporter());
?>