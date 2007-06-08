<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Voucher.php';
require_once 'Intraface/Kernel.php';

class FakeAccountingYear {}

class VoucherTest extends PHPUnit_Framework_TestCase {

    private $year;

    function setUp() {
        $this->year = new FakeAccountingYear;
    }

    function testVoucherCreate() {
        $this->markTestIncomplete('needs updating');
        $voucher = new Voucher($this->year);
        $this->assertFalse($voucher->get('id'));
        $voucher->save(array('text' => 'Description'));
        $new_voucher = new Voucher($this->year, $voucher->get('id'));
        $new_voucher->save(array('text' => 'Description - edited'));
        $this->assertTrue($voucher->get('id') == $new_voucher->get('id'));
        $this->assertTrue($new_voucher->get('text') == 'Description - edited');
    }

    function testVatCalculation() {
        $this->assertTrue((80 + Voucher::calculateVat(100, 25)) == 100);
        $this->assertTrue((100 + Voucher::calculateVat(110, 10)) == 110);
        $this->assertTrue(round((93.40 + Voucher::calculateVat(100.41, 7.5)), 2) == 100.41);
    }

}
?>