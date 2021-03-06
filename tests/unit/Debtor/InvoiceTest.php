<?php
require_once 'Intraface/modules/invoice/Invoice.php';
require_once 'Intraface/Date.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/functions.php';

class InvoiceTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->exec('TRUNCATE debtor');
        $this->db->exec('TRUNCATE debtor_item');
        $this->db->exec('TRUNCATE product');
        $this->db->exec('TRUNCATE product_detail');
        $this->db->exec('TRUNCATE product_detail_translation');
        $this->db->exec('TRUNCATE accounting_account');
        $this->db->exec('TRUNCATE accounting_post');
        $this->db->exec('TRUNCATE accounting_year');
        $this->db->exec('TRUNCATE accounting_voucher');
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        $kernel->setting->set('intranet', 'onlinepayment.provider_key', '1');
        $kernel->setting->set('user', 'accounting.active_year', '1');
        $kernel->setting->set('intranet', 'vatpercent', 25);

        return $kernel;
    }

    function createInvoice()
    {

        return new Invoice($this->createKernel());
    }

    function createAnInvoiceWithOneItem($options = array())
    {

        $options = array_merge(
            array(
                'product_vat' => 1,
                'product_state_account_id' => 1110,
            ),
            $options
        );

        $invoice = $this->createInvoice();
        $invoice->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $invoice->loadItem();

        $product = new Product($this->createKernel());
        $product->save(array('name' => 'test', 'vat' => $options['product_vat'], 'price' => '100', 'state_account_id' => $options['product_state_account_id']));
        $invoice->item->save(array('product_id' => 1, 'quantity' => 2, 'description' => 'This is a test'));

        return $invoice;
    }

    function createAccountingYear()
    {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0));
        $year->createAccounts('standard');
        return $year;
    }

    function createContact()
    {

        $contact = new Contact($this->createKernel());
        return $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
    }

    function testConstruct()
    {

        $invoice = $this->createInvoice();
        $this->assertTrue(is_object($invoice));
    }

    function testSetStatus()
    {
        $invoice = $this->createAnInvoiceWithOneItem();
        $this->assertTrue($invoice->setStatus('sent'));
        $invoice->load();
        $this->assertEquals('sent', $invoice->get('status'));
    }

    function testReadyForStateWithoutCheckingProducts()
    {
        $invoice = $this->createAnInvoiceWithOneItem();
        $year = $this->createAccountingYear();
        $this->assertFalse($invoice->readyForState($year, 'skip_check_products'));

        // needed otherwise errors are transfered...
        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $this->assertTrue($invoice->readyForState($year, 'skip_check_products'), $invoice->error->view());
    }

    function testReadyForStateWithCheckingProducts()
    {

        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $this->assertTrue($invoice->readyForState($this->createAccountingYear()), $invoice->error->view());
    }

    function testState()
    {
        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $year = $this->createAccountingYear();
        $this->assertTrue($invoice->state($year, 1, '10-01-' . date('Y'), new Stub_Translation), $invoice->error->view());

        $voucher = Voucher::factory($year, 1);

        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'invoice #1 - test',
                'debet' => 200.00,
                'credit' => 0.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => '10-01-' . date('Y'),
                'date' =>  date('Y') . '-01-10',
                'text' => 'invoice #1 - test',
                'debet' => 0.00,
                'credit' => 200.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 2,
                'stated' => 1,
                'account_number' => 1110,
                'account_name' => 'Salg med moms'
            ),
            2 => array(
                'id' => 3,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'invoice #1 - Moms, udgående, salg',
                'debet' => 50.00,
                'credit' => 0.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            ),
            3 => array(
                'id' => 4,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'invoice #1 - Moms, udgående, salg',
                'debet' => 0.00,
                'credit' => 50.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 46,
                'stated' => 1,
                'account_number' => 66200,
                'account_name' => 'Moms, udgående, salg'
            )
        );

        $this->assertEquals($expected, $voucher->getPosts());
        $this->assertTrue($invoice->isStated());
        $this->assertFalse($invoice->readyForState($year));
    }

    function testStateStatesNoVatWhenNotVatOnProduct()
    {
        $invoice = $this->createAnInvoiceWithOneItem(array('product_vat' => 0, 'product_state_account_id' => 1120));
        $invoice->setStatus('sent');
        $year = $this->createAccountingYear();
        $this->assertTrue($invoice->state($year, 1, '10-01-' . date('Y'), new Stub_Translation), $invoice->error->view());

        $voucher = Voucher::factory($year, 1);

        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'invoice #1 - test',
                'debet' => 200.00,
                'credit' => 0.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'invoice #1 - test',
                'debet' => 0.00,
                'credit' => 200.00,
                'voucher_number' => 1,
                'reference' => '1',
                'voucher_id' => 1,
                'account_id' => 3,
                'stated' => 1,
                'account_number' => 1120,
                'account_name' => 'Salg uden moms'
            )
        );

        $this->assertEquals($expected, $voucher->getPosts());
        $this->assertTrue($invoice->isStated());
        $this->assertFalse($invoice->readyForState($year));
    }
}
