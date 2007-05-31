<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Account.php';

class FakeAccountSetting
{
    function get()
    {
        return 25;
    }
}

class FakeAccountIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeAccountUser
{
    function get()
    {
        return 1;
    }
}

class FakeAccountKernel
{
    public $intranet;
    public $user;
    public $setting;
    function __construct()
    {
        $this->intranet = new FakeAccountIntranet;
        $this->user = new FakeAccountUser;
        $this->setting = new FakeAccountSetting;
    }
}

class FakeAccountYear
{
    public $kernel;
    function __construct()
    {
        $this->kernel = new FakeAccountKernel;
    }
    function get()
    {
        return 1;
    }
}

class AccountTest extends PHPUnit_Framework_TestCase {

    function testVatCalculation()
    {
        $this->assertEquals((80 + Account::calculateVat(100, 25)), 100);
        $this->assertEquals((100 + Account::calculateVat(110, 10)), 110);
        $this->assertEquals(round((93.40 + Account::calculateVat(100.41, 7.5)), 2), 100.41);
    }

    function createAccount($id = 0)
    {
        return new Account(new FakeAccountYear, $id);
    }

    function testConstruction()
    {
        $account = $this->createAccount();
        $this->assertEquals('Account', get_class($account));
    }

    function testSave()
    {
        $account = $this->createAccount();
        $account_number = rand(1, 1000000);
        $data = array(
            'number' => $account_number,
            'name' => 'test',
            'type_key' => 1,
            'use_key' => 1,
            'vat_key' => 1,
            'vat_percent' => 25
        );
        $id = $account->save($data);

        $this->assertTrue(($id > 0));
    }

    function testSavePrimoSaldo()
    {
        $account = $this->createAccount();
        $account_number = rand(1, 1000000);
        $data = array(
            'number' => $account_number,
            'name' => 'test',
            'type_key' => 1,
            'use_key' => 1,
            'vat_key' => 1,
            'vat_percent' => 25
        );
        $id = $account->save($data);

        $debet = '380.071,97';
        $credit = '0';
        $this->assertTrue($account->savePrimoSaldo($debet, $credit));

        $saldo = $account->getPrimoSaldo();

        $this->assertEquals($saldo['debet'], $debet);
        $this->assertEquals($saldo['credit'], $credit);
    }
    function testUpdatePrimoSaldo()
    {
        $account = $this->createAccount();
        $account_number = rand(1, 1000000);
        $data = array(
            'number' => $account_number,
            'name' => 'test',
            'type_key' => 1,
            'use_key' => 1,
            'vat_key' => 1,
            'vat_percent' => 25
        );
        $id = $account->save($data);

        $debet = '380.071,97';
        $credit = '0';
        $this->assertTrue($account->savePrimoSaldo($debet, $credit));

        $debet_new = '380.071,96';
        $credit_new = '0';

        $this->assertTrue($account->savePrimoSaldo($debet_new, $credit_new));

        $saldo = $account->getPrimoSaldo();

        $this->assertEquals($saldo['debet'], $debet_new);

    }
}
?>