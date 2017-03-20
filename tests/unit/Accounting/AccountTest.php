<?php
require_once 'Intraface/modules/accounting/Account.php';

class FakeAccountYear
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
}

class AccountTest extends PHPUnit_Framework_TestCase
{
    private $delta = 0.001;
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE accounting_account');
    }

    function createAccount($id = 0)
    {
        return new Account(new FakeAccountYear, $id);
    }

    /////////////////////////////////////////////////////////////////////////////////////

    function testVatCalculationReturnsCorrectValues()
    {
        $this->assertEquals((80 + Account::calculateVat(100, 25)), 100);
        $this->assertEquals((100 + Account::calculateVat(110, 10)), 110);
        $this->assertEquals(round((93.40 + Account::calculateVat(100.41, 7.5)), 2), 100.41);
    }

    function testConstructionOfAnAccountObjectReturnsAValidObject()
    {
        $account = $this->createAccount();
        $this->assertTrue(is_object($account));
        $this->assertEquals('Account', get_class($account));
    }

    function testSaveReturnsAnIntegerLargerThanZero()
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

        $debet = '380071,97';
        $credit = '0';
        $this->assertTrue($account->savePrimoSaldo($debet, $credit));

        $saldo = $account->getPrimoSaldo();

        $this->assertEquals('380071.97', $saldo['debet'], '', $this->delta);
        $this->assertEquals($credit, $saldo['credit'], '', $this->delta);
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

        // ATTENTION: test will fail if primosaldo is not a double (not float)
        $debet = '380071,97';
        $credit = '0';
        $this->assertTrue($account->savePrimoSaldo($debet, $credit));

        $debet_new = '380071,96';
        $credit_new = '0';

        $this->assertTrue($account->savePrimoSaldo($debet_new, $credit_new));

        $saldo = $account->getPrimoSaldo();

        $this->assertEquals('380071.96', $saldo['debet'], '', $this->delta);
    }

    function testDeleteActuallyDeletesTheAccount()
    {
        $account = $this->createAccount();
        $this->assertTrue($account->delete());
        $this->assertEquals(0, $account->get('active'));
    }

    function testGetSaldo()
    {
        $account = $this->createAccount();
        // @todo this is strange behaviour
        $this->assertTrue($account->getSaldo());
    }

    function testAnyPostsReturnsZeroWhenNoPostsAreFound()
    {
        $account = $this->createAccount();
        $this->assertEquals(0, $account->anyPosts());
    }

    function testGetPostReturnsAnArray()
    {
        $account = $this->createAccount();
        $this->assertTrue(is_array($account->getPosts()));
    }

    function testAnyAccountsReturnsZeroWhenNoAccountIsSaved()
    {
        $account = $this->createAccount();
        $this->assertTrue($account->anyAccounts() == 0);
    }


    function testAnyAccountsReturnsAnIntegerGreaterThanZeroWhenOneAccountIsSaved()
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
        $this->assertTrue($account->save($data) > 0);

        $this->assertTrue($account->anyAccounts() > 0);
    }

    function testVatPercentIsTransferredWhenSavingAnAccountFromTheValuesOfAnotherAccount()
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
        $this->assertTrue($account->save($data) > 0);
        $this->assertEquals(25, $account->get('vat_percent'));

        $account_new = $this->createAccount();
        $data = $account->get();
        $data['number'] = rand(1, 1000000);
        ;
        $account_new->save($data);
        $this->assertEquals(25, $account_new->get('vat_percent'));
    }

    function testFactory()
    {
        $account = $this->createAccount();
        $factory = Account::factory(new FakeAccountYear, $account->getNumber());
        $this->assertTrue(is_object($factory));
        $this->assertEquals($account->getNumber(), $factory->getNumber());
    }

    function testValidForStateReturnsFalseIfTypeKeyIsNotSet()
    {
        $account = $this->createAccount();
        $res = $account->validForState();
        $this->assertFalse($res);
    }

    function testSaveReturnsTrueWhenTypeKeyIsSet()
    {
        $account = $this->createAccount();
        $account_number = rand(1, 1000000);
        $data = array(
            'number' => $account_number,
            'name' => 'test',
            'type_key' => 2,
            'use_key' => 1,
            'vat_key' => 1,
            'vat_percent' => 25
        );
        $id = $account->save($data);
        $res = $account->validForState();
        $this->assertEquals('operating', $account->getType());
        $this->assertTrue($res);
    }
}
