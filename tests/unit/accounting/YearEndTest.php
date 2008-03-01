<?php
require_once dirname(__FILE__) . './../config.test.php';
require_once 'PHPUnit/Framework.php';
require_once 'Intraface/modules/accounting/YearEnd.php';
require_once 'Intraface/Kernel.php';

class FakeVatPeriodIntranet
{
    function get()
    {
        return 1;
    }

    function hasModuleAccess()
    {
        return true;
    }
}

class FakeVatPeriodUser
{
    function hasModuleAccess()
    {
        return true;
    }

    function get()
    {
        return 1;
    }
}

class FakeVatPeriodSetting
{
    function get()
    {
        return 1;
    }
}

class FakeYearEndYear
{
    public $kernel;
    function __construct()
    {
        $this->kernel = new Kernel;
        $this->kernel->user = new FakeVatPeriodUser;
        $this->kernel->module('accounting');
        $this->kernel->intranet = new FakeVatPeriodIntranet;
        $this->kernel->setting = new FakeVatPeriodSetting;

    }
    function get()
    {
        return 1;
    }

    function getSetting()
    {
        return 1;
    }
}

class FakeVatPeriodAccount
{
    function __construct()
    {

    }
}

class YearEndTest extends PHPUnit_Framework_TestCase
{

    function createYearEnd()
    {
        return new YearEnd(new FakeYearEndYear);
    }


    function testConstruction()
    {
        $vat = $this->createYearEnd();
        $this->assertTrue(is_object($vat));
    }
    /*
    function testCreatePeriods()
    {
        $vat = $this->createPeriod();
        $this->assertTrue($vat->createPeriods());
        $periods = $vat->getList();
        $this->assertTrue(count($periods) == 4);
        $this->assertTrue($periods[0]['date_start'] == '0001-01-01' && $periods[0]['date_end'] == '0001-03-31');
        $this->assertTrue($periods[1]['date_start'] == '0001-04-01' && $periods[1]['date_end'] == '0001-06-30');
        $this->assertTrue($periods[2]['date_start'] == '0001-07-01' && $periods[2]['date_end'] == '0001-09-30');
        $this->assertTrue($periods[3]['date_start'] == '0001-10-01' && $periods[3]['date_end'] == '0001-12-31');
    }
    */

    /*
    function testLoadAmounts()
    {
        // TODO needs to be updated
        $this->markTestIncomplete('could not seem to get this under test - to many strange dependencies');
        $vat = $this->createPeriod();
        $this->assertTrue($vat->loadAmounts());
    }
    */
    /*
    function testState()
    {
        $vat = $this->createPeriodWithFakeLoadAmounts();
        // $this->assertTrue($vat->state());
    }
    */

}