<?php
require_once dirname(__FILE__) . './../config.test.php';
require_once 'Intraface/modules/accounting/Account.php';
require_once 'Intraface/modules/accounting/YearEnd.php';
require_once 'Intraface/Kernel.php';

class FakeYearEndYear
{
    public $kernel;
    function __construct()
    {
        $this->kernel = new Stub_Kernel;
        $this->kernel->setting->set('intranet', 'vatpercent', 25);

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

class FakeYearEndAccount extends Account
{
    function __construct($year)
    {
        $this->value['number'] = 100;
        parent::__construct($year);
    }
}

class TestableYearEnd extends YearEnd
{
    function getAccount()
    {
        return new FakeYearEndAccount($this->year);
    }
}

class YearEndTest extends PHPUnit_Framework_TestCase
{

    private $end;

    function setUp()
    {
        $this->end = $this->createYearEnd();
    }

    function tearDown()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE accounting_year_end');
        $db->exec('TRUNCATE accounting_year_end_action');
        $db->exec('TRUNCATE accounting_year_end_statement');
        $db->exec('TRUNCATE setting');
        $db->exec('TRUNCATE accounting_year');
        $db->exec('TRUNCATE accounting_account');
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
        $end = new TestableYearEnd(new FakeYearEndYear);
        $this->assertTrue($end->saveStatement('operating'));
        $this->assertTrue($end->saveStatement('balance'));
    }

    function testResetOperatingAccountsReturnsFalseWhenNoAccountsIsFound()
    {
        $result = $this->end->resetOperatingAccounts();
        $this->assertFalse($result);
    }

    function testResetYearResultReturnsTrue()
    {
        $this->assertTrue($this->end->resetYearResult());

    }
}