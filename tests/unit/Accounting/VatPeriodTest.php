<?php
require_once 'Intraface/modules/accounting/VatPeriod.php';
require_once 'Intraface/modules/accounting/Account.php';
require_once 'Intraface/modules/accounting/Voucher.php';
require_once 'Intraface/Kernel.php';

class FakeVatPeriodYear
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

    function getSetting($key = '')
    {
        if ($key == 'vat_period') {
            return 1;
        }
        return serialize(array());
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

class FakeVatPeriodAccount extends Account
{
    protected $id;

    function __construct($year, $id = 0)
    {
        $this->id = (int)$id;
        $this->value['number'] = 1000;
        $this->value['saldo'] = 100;
    }

    function getSaldo($type, $date_from, $date_to)
    {

    }

    function getId()
    {
        return $this->id;
    }

    function validForState()
    {
        return true;
    }
}

class FakeVatPeriodVoucher extends Voucher
{
    function getAccount($id)
    {
        return new FakeVatPeriodAccount(new FakeVatPeriodYear);
    }
}

class TestableVatPeriod extends VatPeriod
{
    function getId()
    {
        return 1;
    }

    function getAccount($id)
    {
        return new FakeVatPeriodAccount(new FakeVatPeriodYear, $id);
    }

    function getVoucher()
    {
        return new FakeVatPeriodVoucher(new FakeVatPeriodYear);
    }

    function loadAmounts()
    {
        $saldo_total = 0; // integer med total saldo
        $saldo_rubrik_a = 0;

        $date_from = $this->get('date_start');
        $date_to = $this->get('date_end');

        // Salgsmoms - udg�ende
        $account_vat_in = $this->getAccount(1);
        $account_vat_in->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_out'] = $account_vat_in;

        // ganges med -1 for at f� rigtigt fortegn til udregning
        $this->value['saldo_vat_out'] = $account_vat_in->get('saldo');
        $saldo_total += -1 * $this->value['saldo_vat_out']; // total


        // Moms af varek�b i udlandet
        // Dette bel�b er et udregnet bel�b, som udregnes under bogf�ringen
        $account_vat_abroad = $this->getAccount(2);
        $account_vat_abroad->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_abroad'] = $account_vat_abroad;

        // ganges med -1 for at f� rigtigt fortegn til udregning
        $this->value['saldo_vat_abroad'] = $account_vat_abroad->get('saldo');
        $saldo_total += -1 * $this->value['saldo_vat_abroad'];

        // K�bsmoms
        // K�bsmomsen inkluderer ogs� den udregnede moms af moms af varek�b i udlandet.
        // Dette bel�b er lagt p� kontoen under bogf�ringen.
        $account_vat_out = $this->getAccount(3);
        $account_vat_out->getSaldo('stated', $date_from, $date_to);
        $this->value['account_vat_in'] = $account_vat_out;

        $this->value['saldo_vat_in'] = $account_vat_out->get('saldo');
        $saldo_total -= $this->value['saldo_vat_in'];


        // Rubrik A
        // EU-erhvervelser - her samles forskellige konti og bel�bet udregnes.
        // Primosaldoen skal ikke medregnes
        $buy_eu_accounts = unserialize($this->year->getSetting('buy_eu_accounts'));
        $this->value['saldo_rubrik_a'] = 0;
        $saldo_rubrik_a = 0;

        if (is_array($buy_eu_accounts) and count($buy_eu_accounts) > 0) {
            foreach ($buy_eu_accounts as $key => $id) {
                $account_eu_buy = new FakeVatPeriodAccount($this->year, $id);
                $primo = $account_eu_buy->getPrimoSaldo();
                $account_eu_buy->getSaldo('stated', $date_from, $date_to);
                $saldo_rubrik_a += $account_eu_buy->get('saldo');
                $saldo_rubrik_a -= $primo['saldo'];
            }
        }
        $this->value['saldo_rubrik_a'] = $saldo_rubrik_a;

        // Rubrik B
        // V�rdien af varesalg uden moms til andre EU-lande (EU-leverancer). Udfyldes
        // denne rubrik, skal der indsendes en liste med varesalgene uden moms.

        // Vi underst�tter ikke rubrikken

        // Rubrik C
        // V�rdien af varesalg uden moms til andre EU-lande (EU-leverancer). Udfyldes
        // denne rubrik, skal der indsendes en liste med varesalgene uden moms.

        // Vi underst�tter ikke rubrikken

        $this->value['saldo_total'] = $saldo_total;

        return true;
    }
}

class VatPeriodTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE accounting_account');
    }

    function createPeriod()
    {
        return new VatPeriod(new FakeVatPeriodYear);
    }

    function createPeriodWithFakeLoadAmounts()
    {
        return new TestableVatPeriod(new FakeVatPeriodYear);
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

    function testStateReturnsTrue()
    {
        // @todo make sure that the getSetting returns some usable accounts.
        $vat = $this->createPeriodWithFakeLoadAmounts();
        $this->assertTrue($vat->state(date('d-m-Y'), 100));
    }

    function testIsBalancedReturnsTrueWhenNoAmountsHasBeenSaved()
    {
        $vat = $this->createPeriod();
        $this->assertTrue($vat->isBalanced());
    }

    function testDeleteReturnsTrue()
    {
        $vat = $this->createPeriod();
        $this->assertTrue($vat->delete());
    }
}
