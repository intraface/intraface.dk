<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/invoice/Invoice.php';
require_once 'Intraface/tools/Date.php';
require_once 'tests/unit/stubs/Kernel.php';
require_once 'tests/unit/stubs/User.php';
require_once 'tests/unit/stubs/Intranet.php';
require_once 'tests/unit/stubs/Setting.php';
require_once 'tests/unit/stubs/Address.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/contact/Contact.php';

class InvoiceTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    
    function setUp() {
        
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE debtor');
        $db->query('TRUNCATE debtor_item');
        $db->query('TRUNCATE product');
        $db->query('TRUNCATE product_detail');
        
    }
    
    function createKernel() {
        $kernel = new FakeKernel;
        $kernel->user = new FakeUser;
        $kernel->intranet = new FakeIntranet;
        $kernel->setting = new FakeSetting;
        $kernel->intranet->address = new FakeAddress;
        $kernel->setting->set('intranet', 'onlinepayment.provider_key', '1');
        $kernel->setting->set('user', 'accounting.active_year', '1');
        $kernel->setting->set('intranet', 'vatpercent', 25);
        
        return $kernel;
    }
    
    function createInvoice()
    {
        
        return new Invoice($this->createKernel());
    }
    
    function createAnInvoiceWithOneItem() {
        
        $invoice = $this->createInvoice();
        $invoice->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        
        $invoice->loadItem();
        
        $product = new Product($this->createKernel());
        $product->save(array('name' => 'test', 'vat' => 1, 'price' => '100', 'state_account_id' => 2));
        $invoice->item->save(array('product_id' => 1, 'quantity' => 2, 'description' => 'This is a test'));
        
        return $invoice;
    }
    
    function createAccountingYear() {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0));
        $year->createAccounts('standard');
        return $year;
    }
    
    function createContact() {
        
        $contact = new Contact($this->createKernel());
        return $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
    }

    function testConstruct()
    {
        
        $invoice = $this->createInvoice();
        $this->assertTrue(is_object($invoice));
    }
    
    function testSetStatus() {
        $invoice = $this->createAnInvoiceWithOneItem();
        $this->assertTrue($invoice->setStatus('sent'));
        $invoice->load();
        $this->assertEquals('sent', $invoice->get('status'));
    }
    
    function testReadyForStateWithoutCheckingProducts() {
        $invoice = $this->createAnInvoiceWithOneItem();
        $this->assertFalse($invoice->readyForState('skip_check_products'));
        
        // needed otherwise errors are transfered...
        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $this->assertTrue($invoice->readyForState('skip_check_products'), $invoice->error->view());
    }
    
    function testReadyForStateWithCheckingProducts() {
        
        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $this->assertTrue($invoice->readyForState(), $invoice->error->view());
    }
    
    function testState() {
        $invoice = $this->createAnInvoiceWithOneItem();
        $invoice->setStatus('sent');
        $this->assertTrue($invoice->state($this->createAccountingYear(), 1, '10-01-2008'), $invoice->error->view());
    }

}
?>
