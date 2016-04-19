<?php
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/procurement/Procurement.php';
require_once 'DB/Sql.php';

class ProcurementTest extends PHPUnit_Framework_TestCase
{
    private $kernel;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->exec('TRUNCATE procurement');
        $db->exec('TRUNCATE procurement_item');
        $db->exec('TRUNCATE accounting_account');
        $db->exec('TRUNCATE accounting_post');
        $db->exec('TRUNCATE accounting_year');
        $db->exec('TRUNCATE accounting_voucher');

    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        $kernel->setting->set('user', 'accounting.active_year', '1');
        $kernel->setting->set('intranet', 'vatpercent', 25);
        $kernel->setting->set('intranet', 'accounting.vat_out_account_id', 46);
        $kernel->setting->set('intranet', 'accounting.vat_in_account_id', 44);

        return $kernel;
    }

    function createAccountingYear()
    {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0, 'vat' => 1));
        $year->createAccounts('standard');
        return $year;
    }

    function testConstruct()
    {
        $procurement = new Procurement($this->createKernel());
        $this->assertEquals('Procurement', get_class($procurement));
    }

    function testUpdateWithEmptyArray()
    {
        $procurement = new Procurement($this->createKernel());

        $this->assertFalse($procurement->update(array()));
        $this->assertEquals(3, $procurement->error->count(), $procurement->error->view());

    }

    function testUpdateWithValidInput()
    {
        $procurement = new Procurement($this->createKernel());
        $this->assertTrue($procurement->update(array('dk_invoice_date' => '01-01-2007', 'delivery_date' => '02-01-2007', 'dk_payment_date' => '03-01-2007', 'number' => 1, 'description' => 'test', 'dk_price_items' => '100,00', 'dk_price_shipment_etc' => '40,00', 'dk_vat' => '25,00')));
    }

    function testLoad()
    {
        $kernel = $this->createKernel();
        $procurement = new Procurement($kernel);
        $procurement->update(array('dk_invoice_date' => '01-01-2007', 'delivery_date' => '02-01-2007', 'dk_payment_date' => '03-01-2007', 'number' => 1, 'description' => 'test', 'dk_price_items' => '100,00', 'dk_price_shipment_etc' => '40,00', 'dk_vat' => '25,00'));

        $procurement = new Procurement($kernel, 1);
        $expected = array(
            'id' => 1,
            'invoice_date' => '2007-01-01',
            'dk_invoice_date' => '01-01-2007',
            'delivery_date' => '2007-01-01',
            'dk_delivery_date' => '01-01-2007',
            'payment_date' => '2007-01-03',
            'dk_payment_date' => '03-01-2007',
            'date_recieved' => '0000-00-00 00:00:00',
            'dk_date_recieved' => '00-00-0000',
            'date_canceled' => '0000-00-00 00:00:00',
            'dk_date_canceled' => '00-00-0000',
            'date_stated' => '00-00-0000',
            'dk_date_stated' => '00-00-0000',
            'paid_date' => '0000-00-00',
            'this_date' => '0000-00-00',
            'dk_paid_date' => '00-00-0000',
            'number' => 1,
            'contact_id' => 0,
            'vendor' => '',
            'description' => 'test',
            'from_region_key' => 0,
            'from_region' => 'denmark',
            'price_items' => '100.00',
            'dk_price_items' => '100,00',
            'price_shipment_etc' => '40.00',
            'dk_price_shipment_etc' => '40,00',
            'vat' => '25.00',
            'dk_vat' => '25,00',
            'total_price' => 165,
            'dk_total_price' => '165,00',
            'status_key' => 0,
            'status' => 'ordered',
            'state_account_id' => 0,
            'voucher_id' => 0
        );

        $this->assertEquals($expected, $procurement->get());

    }

    function testReadyForStateBeforeSaved()
    {
        $procurement = new Procurement($this->createKernel());
        $this->assertFalse($procurement->readyForState($this->createAccountingYear()));
    }

    function testReadyForStateWhenReady()
    {
        $procurement = new Procurement($this->createKernel());
        $procurement->update(array('dk_invoice_date' => '01-01-'.date('Y'), 'delivery_date' => '02-01-'.date('Y'), 'dk_payment_date' => '03-01-'.date('Y'), 'number' => 1, 'description' => 'test', 'dk_price_items' => '100,00', 'dk_price_shipment_etc' => '40,00', 'dk_vat' => '25,00'));
        $procurement->setPaid('04-01-'.date('Y'));
        $this->assertTrue($procurement->readyForState($this->createAccountingYear()));
    }

    function testIsStateBeforeStated()
    {
        $procurement = new Procurement($this->createKernel());
        $this->assertFalse($procurement->isStated());
    }

    function testState()
    {
        $procurement = new Procurement($this->createKernel());
        $procurement->update(array('dk_invoice_date' => '01-01-'.date('Y'), 'delivery_date' => '02-01-'.date('Y'), 'dk_payment_date' => '03-01-'.date('Y'), 'number' => 1, 'description' => 'test', 'dk_price_items' => '100,00', 'dk_price_shipment_etc' => '40,00', 'dk_vat' => '25,00'));
        $year = $this->createAccountingYear();
        $procurement->setPaid('04-01-'.date('Y'));

        $state = array(
            0 => array('text' => '', 'amount' => '100,00', 'state_account_id' => 7000),
            1 => array('text' => 'shipment_etc', 'amount' => '40,00', 'state_account_id' => 7200)

        );

        $this->assertTrue($procurement->state($year, 1, '05-01-'.date('Y'), $state, 58000, new Stub_Translation), $procurement->error->view());

        $voucher = Voucher::factory($year, 1);
        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '05-01-' . date('Y'),
                'date' => date('Y') . '-01-05',
                'text' => 'procurement# 1: test - købsmoms',
                'debet' => '25.00',
                'credit' => '0.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 44,
                'stated' => 1,
                'account_number' => '66100',
                'account_name' => 'Moms, indgående, køb'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => '05-01-' . date('Y'),
                'date' => date('Y') . '-01-05',
                'text' => 'procurement# 1: test',
                'debet' => '100.00',
                'credit' => '0.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 10,
                'stated' => 1,
                'account_number' => '7000',
                'account_name' => 'Kontorartikler'
            ),
            2 => array(
                'id' => 3,
                'date_dk' => '05-01-' . date('Y'),
                'date' => date('Y') . '-01-05',
                'text' => 'procurement# 1: test',
                'debet' => '0.00',
                'credit' => '125.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 33,
                'stated' => 1,
                'account_number' => '58000',
                'account_name' => 'Bank, folio'
            ),
            3 => array(
                'id' => 4,
                'date_dk' => '05-01-' . date('Y'),
                'date' => date('Y') . '-01-05',
                'text' => 'procurement# 1: test - shipment_etc',
                'debet' => '40.00',
                'credit' => '0.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 11,
                'stated' => 1,
                'account_number' => '7200',
                'account_name' => 'Porto'
            ),
            4 => array(
                'id' => 5,
                'date_dk' => '05-01-' . date('Y'),
                'date' => date('Y') . '-01-05',
                'text' => 'procurement# 1: test - shipment_etc',
                'debet' => '0.00',
                'credit' => '40.00',
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 33,
                'stated' => 1,
                'account_number' => '58000',
                'account_name' => 'Bank, folio'
            )
        );

        $this->assertEquals($expected, $voucher->getPosts());

        $this->assertTrue($procurement->isStated());
        $this->assertFalse($procurement->readyForState($year));
    }

    function testStateWithNoShipment()
    {
        $procurement = new Procurement($this->createKernel());
        $procurement->update(array('dk_invoice_date' => '01-01-'.date('Y'), 'delivery_date' => '02-01-'.date('Y'), 'dk_payment_date' => '03-01-'.date('Y'), 'number' => 1, 'description' => 'test', 'dk_price_items' => '135,96', 'dk_price_shipment_etc' => '0', 'dk_vat' => '33,99'));
        $year = $this->createAccountingYear();
        $procurement->setPaid('04-01-'.date('Y'));

        $state = array(
            0 => array('text' => '', 'amount' => '135,96', 'state_account_id' => 7000),

        );


        $this->assertTrue($procurement->state($year, 1, '05-01-'.date('Y'), $state, 58000, new Stub_Translation), $procurement->error->view());
    }
}
