<?php
require_once dirname(__FILE__) . './../config.test.php';
require_once 'PHPUnit/Framework.php';
require_once 'Intraface/modules/accounting/VatPeriod.php';
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

class FakeVatPeriodYear
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

class VatPeriodTest extends PHPUnit_Framework_TestCase
{

    function createPeriod()
    {
        return new VatPeriod(new FakeVatPeriodYear);
    }

    function testConstruction()
    {
        $vat = $this->createPeriod();
        $this->assertTrue(is_object($vat));
    }

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
}
?>