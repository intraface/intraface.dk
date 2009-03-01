<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SeleniumTests extends PHPUnit_Extensions_SeleniumTestCase
{
    public static $seleneseDirectory = '../selenium/';
    //protected $coverageScriptUrl = 'http://workspace/phpunit_coverage.php';

    protected function setUp()
    {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('http://workspace/intraface/intraface.dk/');
    }

    function assertConfirmation()
    {
    	return $this->assertPromptPresent();
    }

    function assertSelectedValue($selectLocator, $option)
    {
    	return $this->assertSelected($selectLocator, $option);
    }

    function verifyValue($pattern)
    {
    	return $this->assertTextPresent($pattern);
    }

}