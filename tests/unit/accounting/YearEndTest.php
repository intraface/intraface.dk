<?php
require_once dirname(__FILE__) . './../config.test.php';
require_once 'PHPUnit/Framework.php';
require_once 'Intraface/modules/accounting/YearEnd.php';
require_once 'Intraface/Kernel.php';

class FakeYearEndIntranet
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

class FakeYearEndUser
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

class FakeYearEndSetting
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
        $this->kernel->user = new FakeYearEndUser;
        $this->kernel->module('accounting');
        $this->kernel->intranet = new FakeYearEndIntranet;
        $this->kernel->setting = new FakeYearEndSetting;

    }
    function get()
    {
        return 1;
    }

    function getSetting()
    {
        return 1;
    }

    function isYearOpen()
    {
        return true;
    }

    function isDateInYear()
    {
        return true;
    }
}

class FakeYearEndAccount
{
    function __construct()
    {

    }
}

class YearEndTest extends PHPUnit_Framework_TestCase
{

    private $end;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE accounting_year_end');
        $db->exec('TRUNCATE accounting_year_end_action');
        $db->exec('TRUNCATE accounting_year_end_statement');
        $this->end = $this->createYearEnd();
    }

    function createYearEnd()
    {
        return new YearEnd(new FakeYearEndYear);
    }

    function testConstruction()
    {
        $vat = $this->createYearEnd();
        $this->assertTrue(is_object($vat));
    }

    function testStartReturnsTrue()
    {
        $this->assertTrue($this->end->start());
    }

    function testSetStepReturnsTrue()
    {
        $this->assertTrue($this->end->setStep(1));
    }

    function testSetStatedReturnsTrue()
    {
        $this->assertTrue($this->end->setStated('operating_reset', 100));
        $this->assertTrue($this->end->setStated('result_account_reset', 100));
    }

    function testGetStatedActionsReturnsArray()
    {
        $this->assertTrue(is_array($this->end->getStatedActions('operating_reset')));
        $this->assertTrue(is_array($this->end->getStatedActions('result_account_reset')));
    }

    function testSaveStatement()
    {
        $this->assertTrue($this->end->saveStatement('operating'));
        $this->assertTrue($this->end->saveStatement('balance'));
    }

    function testResetOperatingAccountsReturnsTrue()
    {
        $this->assertTrue($this->end->resetOperatingAccounts());
    }

    function testResetYearResultReturnsTrue()
    {
        $this->assertTrue($this->end->resetYearResult());

    }
}