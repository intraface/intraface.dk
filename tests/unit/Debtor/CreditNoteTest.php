<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/invoice/CreditNote.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/contact/Contact.php';
require_once 'Intraface/functions.php';

class CreditNoteTest extends PHPUnit_Framework_TestCase
{
    private $kernel;

    function setUp() {

        $db = MDB2::factory(DB_DSN);
        $db->exec('TRUNCATE debtor');
        $db->exec('TRUNCATE debtor_item');
        $db->exec('TRUNCATE product');
        $db->exec('TRUNCATE product_detail');
        $db->exec('TRUNCATE product_detail_translation');
        $db->exec('TRUNCATE accounting_account');
        $db->exec('TRUNCATE accounting_post');
        $db->exec('TRUNCATE accounting_year');
        $db->exec('TRUNCATE accounting_voucher');

    }

    function createKernel() {
        $kernel = new Stub_Kernel;
        $kernel->setting->set('intranet', 'onlinepayment.provider_key', '1');
        $kernel->setting->set('user', 'accounting.active_year', '1');
        $kernel->setting->set('intranet', 'vatpercent', 25);

        return $kernel;
    }

    function createCreditNote()
    {

        return new CreditNote($this->createKernel());
    }

    function createAnCreditNoteWithOneItem($options = array()) {
        $options = array_merge(
            array(
                'product_vat' => 1,
                'product_state_account_id' => 1110,
            ),
            $options
        );

        $creditnote = $this->createCreditNote();
        $creditnote->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $creditnote->loadItem();

        $product = new Product($this->createKernel());
        $product->save(array('name' => 'test', 'vat' => $options['product_vat'], 'price' => '100', 'state_account_id' => $options['product_state_account_id']));
        $creditnote->item->save(array('product_id' => 1, 'quantity' => 2, 'description' => 'This is a test'));

        return $creditnote;
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
        $creditnote = $this->createCreditNote();
        $this->assertTrue(is_object($creditnote));
    }

    function testSetStatusToSent()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $this->assertTrue($creditnote->setStatus('sent'));
        $creditnote->load();
        $this->assertEquals('executed', $creditnote->get('status'));
    }

    function testSetStatusToExecuted()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $this->assertTrue($creditnote->setStatus('executed'));
        $creditnote->load();
        $this->assertEquals('executed', $creditnote->get('status'));
    }

    function testReadyForStateWithoutCheckingProducts()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $this->assertFalse($creditnote->readyForState($this->createAccountingYear(), 'skip_check_products'));

        // needed otherwise errors are transfered...
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $creditnote->setStatus('sent');
        $this->assertTrue($creditnote->readyForState($this->createAccountingYear(), 'skip_check_products'), $creditnote->error->view());
    }

    function testReadyForStateWithCheckingProducts()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $creditnote->setStatus('sent');
        $this->assertTrue($creditnote->readyForState($this->createAccountingYear()), $creditnote->error->view());
    }

    function testState()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem();
        $creditnote->setStatus('sent');
        $year = $this->createAccountingYear();
        $this->assertTrue($creditnote->state($year, 1, '10-01-' . date('Y'), new Stub_Translation), 'state: '.$creditnote->error->view());

        $voucher = Voucher::factory($year, 1);

        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'credit note #1 - test',
                'debet' => 200,
                'credit' => 0,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 2,
                'stated' => 1,
                'account_number' => 1110,
                'account_name' => 'Salg med moms'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y'). '-01-10',
                'text' => 'credit note #1 - test',
                'debet' => 0,
                'credit' => 200,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            ),
            2 => array(
                'id' => 3,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'credit note #1 - Moms, udgående, salg',
                'debet' => 50.00,
                'credit' => 0.00,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 46,
                'stated' => 1,
                'account_number' => 66200,
                'account_name' => 'Moms, udgående, salg'
            ),
            3 => array(
                'id' => 4,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y'). '-01-10',
                'text' => 'credit note #1 - Moms, udgående, salg',
                'debet' => 0,
                'credit' => 50,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            )
        );

        $this->assertEquals($expected, $voucher->getPosts());
        $this->assertTrue($creditnote->isStated());
        $this->assertFalse($creditnote->readyForState($year));
    }

    function testStateStatesNoVatWhenNotVatOnProduct()
    {
        $creditnote = $this->createAnCreditNoteWithOneItem(array('product_vat' => 0, 'product_state_account_id' => 1120));
        $creditnote->setStatus('sent');
        $year = $this->createAccountingYear();
        $this->assertTrue($creditnote->state($year, 1, '10-01-' . date('Y'), new Stub_Translation), 'state: '.$creditnote->error->view());

        $voucher = Voucher::factory($year, 1);

        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'credit note #1 - test',
                'debet' => 200,
                'credit' => 0,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 3,
                'stated' => 1,
                'account_number' => 1120,
                'account_name' => 'Salg uden moms'
            ),
            1 => array(
                'id' => 2,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y'). '-01-10',
                'text' => 'credit note #1 - test',
                'debet' => 0,
                'credit' => 200,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 32,
                'stated' => 1,
                'account_number' => 56100,
                'account_name' => 'Debitor'
            )
        );

        $this->assertEquals($expected, $voucher->getPosts());
        $this->assertTrue($creditnote->isStated());
        $this->assertFalse($creditnote->readyForState($year));
    }

}