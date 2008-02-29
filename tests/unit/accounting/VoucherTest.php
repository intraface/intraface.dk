<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Voucher.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/tools/Date.php';

class FakeVoucherSetting {
    function get() {}
}

class FakeVoucherIntranet {
    function get() { return 1; }
}
class FakeVoucherUser {
    function get() { return 1; }
}

class FakeVoucherKernel {

    public $setting;
    public $intranet;
    public $user;
    function __construct() {
        $this->setting = new FakeVoucherSetting;
        $this->intranet = new FakeVoucherIntranet;
        $this->user = new FakeVoucherUser;
    }
}

class FakeAccountingYear {
    public $kernel;
    function __construct() {
        $this->kernel = new FakeVoucherKernel;
    }
    function get() { return 1; }
    function vatAccountIsSet() { return true; }
}

class VoucherTest extends PHPUnit_Framework_TestCase
{

    private $year;

    function setUp()
    {
        $this->year = new FakeAccountingYear;
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

    function testStateDraftReturnsZeroWhenNoPostsAreFound()
    {
        $voucher = new Voucher($this->year);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->stateDraft() == 0);
    }

    function testStateVoucherReturnsOneWhenNoPostsAreFound()
    {
        $voucher = new Voucher($this->year);
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->stateVoucher() == 1);
    }

}