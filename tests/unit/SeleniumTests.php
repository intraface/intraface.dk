<?php
require_once 'PHPUnit/Extensions/Selenium2TestCase.php';

class SeleniumTests extends PHPUnit_Extensions_Selenium2TestCase
{
    public static $seleneseDirectory = '../selenium/';
    //protected $coverageScriptUrl = 'http://workspace/phpunit_coverage.php';
    protected $captureScreenshotOnFailure = true;
    protected $screenshotPath = '/home/lsolesen/workspace/screenshots';
    protected $screenshotUrl = 'http://localhost/screenshots';
    
    protected function setUp()
    {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl($GLOBALS['selenium_url']);
        //$this->setSleep(10);
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
