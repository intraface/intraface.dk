<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Voucher.php';
require_once 'Intraface/Kernel.php';

class VoucherTest extends PHPUnit_Framework_TestCase {

    private $year;

    function setUp() {
        /*
        $kernel = new Kernel();
        $kernel->login('start@intraface.dk', 'startup');
        $kernel->isLoggedIn();
        $kernel->useModule('accounting');
        $year = new Year($kernel);
        $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31'));


         these checks should be elsewhere
        $year = new Year($kernel);
        $this->assertFalse($year->get('id'));
        $this->assertTrue($year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
        $this->assertEqual('2000', $year->get('label'));
        $new_year = new Year($kernel, $year->get('id'), false);
        $this->assertTrue($new_year->save(array('label' => '2000 - edited', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
        $this->assertTrue($new_year->get('id') == $year->get('id'));
        $this->assertTrue($new_year->get('label') == '2000 - edited');
        */

        $this->year = $year;
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