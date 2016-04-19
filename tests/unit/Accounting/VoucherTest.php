<?php
require_once 'Intraface/modules/accounting/Voucher.php';
require_once 'Intraface/modules/accounting/VoucherFile.php';

class FakeVoucherYear
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
    function vatAccountIsSet()
    {
        return true;
    }
    function getSetting()
    {
        return 1;
    }
}

class VoucherTest extends PHPUnit_Framework_TestCase
{
    private $year;

    function setUp()
    {
        $this->year = new FakeVoucherYear;
    }

    function testVoucherCreate()
    {
        // TODO needs to be updated
        $voucher = new Voucher($this->year);
        $this->assertFalse($voucher->get('id') > 0);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $new_voucher = new Voucher($this->year, $voucher->get('id'));
        $new_voucher->save(array('text' => 'Description - edited', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->get('id') == $new_voucher->get('id'));
        $this->assertTrue($new_voucher->get('text') == 'Description - edited');
    }

    function testVatCalculation()
    {
        $this->assertTrue((80 + Voucher::calculateVat(100, 25)) == 100);
        $this->assertTrue((100 + Voucher::calculateVat(110, 10)) == 110);
        $this->assertTrue(round((93.40 + Voucher::calculateVat(100.41, 7.5)), 2) == 100.41);
    }

    function testDeleteReturnsTrue()
    {
        $voucher = new Voucher($this->year);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->delete());
    }

    function testStateDraftReturnsFalseWhenNoPostsAreFound()
    {
        $voucher = new Voucher($this->year);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $res = $voucher->stateDraft();
        $this->assertFalse($res);
    }

    function testStateVoucherReturnsTrueWhenNoPostsAreFound()
    {
        $voucher = new Voucher($this->year);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->stateVoucher());
    }

    function testGetList()
    {
        $voucher = new Voucher($this->year);
        $this->assertTrue(is_array($voucher->getList()));
    }
}
