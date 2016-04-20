<?php
require_once 'Intraface/modules/accounting/Year.php';
require_once 'Intraface/functions.php';

class YearTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->kernel = new Stub_Kernel();
        $this->kernel->setting->set('intranet', 'vatpercent', 25);
        $this->kernel->setting->set('user', 'accounting.active_year', 0);
        $this->kernel->setting->set('intranet', 'accounting.result_account_id', 25);
        $this->kernel->setting->set('intranet', 'accounting.debtor_account_id', 25);
        $this->kernel->setting->set('intranet', 'accounting.credit_account_id', 25);
        $this->kernel->setting->set('intranet', 'accounting.balance_accounts', serialize(array()));
        $this->kernel->setting->set('intranet', 'accounting.result_account_id_start', 0);
        $this->kernel->setting->set('intranet', 'accounting.result_account_id_end', 0);
        $this->kernel->setting->set('intranet', 'accounting.balance_account_id_start', 0);
        $this->kernel->setting->set('intranet', 'accounting.balance_account_id_end', 0);
        $this->kernel->setting->set('intranet', 'accounting.capital_account_id', 0);

        $this->kernel->setting->set('intranet', 'accounting.vat_in_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.vat_out_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.vat_abroad_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.vat_balance_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.vat_free_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.eu_sale_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.result_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.debtor_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.credit_account_id', 0);
        $this->kernel->setting->set('intranet', 'accounting.balance_accounts', serialize(array()));
        $this->kernel->setting->set('intranet', 'accounting.buy_eu_accounts', serialize(array()));
        $this->kernel->setting->set('intranet', 'accounting.buy_abroad_accounts', serialize(array()));

        $this->kernel->setting->set('intranet', 'accounting.result_account_id_start', 0);
        $this->kernel->setting->set('intranet', 'accounting.result_account_id_end', 0);
        $this->kernel->setting->set('intranet', 'accounting.balance_account_id_start', 0);
        $this->kernel->setting->set('intranet', 'accounting.balance_account_id_end', 0);

        $this->kernel->setting->set('intranet', 'accounting.capital_account_id', 0);
    }

    function tearDown()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE accounting_year');
    }

    function testSetYearReturnsFalseWhenYearObjectIsNotSet()
    {
        $year = new Year($this->kernel);
        $this->assertFalse($year->setYear());
    }

    function testSetYearReturnsTrueWhenYearObjectIsSet()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertEquals($id, $year->getActiveYear());
    }

    function testCheckYearReturnsTrueIfActiveYearIsset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertTrue($year->checkYear());
    }

    function testCheckYearReturnsFalseIfActiveYearIsNotset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertFalse($year->checkYear(false));
    }

    function testIsYearSetReturnsTrueWhenYearIsset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertTrue($year->isYearSet());
    }

    function testSaveMethod()
    {
        // TODO needs to be updated
        $year = new Year($this->kernel);
        $this->assertFalse($year->get('id') > 0);
        $this->assertTrue($year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0)) > 0);
        $this->assertEquals('2000', $year->get('label'));

        $new_year = new Year($this->kernel, $year->get('id'), false);
        $this->assertTrue($new_year->save(array('label' => '2000 - edited', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0)) > 0);
        $this->assertEquals($new_year->get('id'), $year->get('id'));
        $this->assertEquals($new_year->get('label'), '2000 - edited');
    }

    function testIsBalancedReturnsTrueWhenNoPostsHasBeenAdded()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($year->isBalanced());
    }

    function testGetList()
    {
        $year = new Year($this->kernel);
        $this->assertTrue(is_array($year->getList()));
    }

    function testGetBalanceAccountsThrowsEceptionIfBalanceAccountsIsNotAnArray()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));

        try {
            $year->getBalanceAccounts();
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    function testCreateAccounts()
    {
        $year = new Year($this->kernel);
        $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $res = $year->createAccounts('standard');
        $this->assertTrue($res);
    }

    function testIsSettingsSet()
    {
        $year = new Year($this->kernel);
        $this->assertFalse($year->isSettingsSet());
    }

    function testGetSettings()
    {
        $year = new Year($this->kernel);
        $this->assertTrue(is_array($year->getSettings()));
    }

    function testSetSettings()
    {
        $year = new Year($this->kernel);
        $data = array();
        $this->assertTrue($year->setSettings($data));
    }

    function testReadyForState()
    {
        $year = new Year($this->kernel);
        $data = array();
        $this->assertFalse($year->readyForState());
    }

    function testIsYearOpen()
    {
        $year = new Year($this->kernel);
        $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($year->isYearOpen());
    }

    function testIsDateInYear()
    {
        $year = new Year($this->kernel);
        $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertFalse($year->isDateinYear(date('Y-m-d')));
    }

    function testVatAccountIsSetReturnsTrueWhenVatOnYearIsNotSet()
    {
        $year = new Year($this->kernel);
        $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($year->vatAccountIsSet());
    }
}
