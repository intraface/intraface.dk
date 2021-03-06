<?php
require_once 'Intraface/modules/invoice/Reminder.php';
require_once 'Intraface/modules/invoice/Invoice.php';
require_once 'Intraface/functions.php';
require_once 'Intraface/modules/contact/Contact.php';

class ReminderTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    protected $db;

    function setUp()
    {
        $this->db = MDB2::singleton(DB_DSN);
    }

    function tearDown()
    {
        $this->db->query('TRUNCATE invoice_reminder');
        $this->db->query('TRUNCATE invoice_reminder_item');
        $this->db->query('TRUNCATE invoice_reminder_unpaid_reminder');
        $this->db->exec('TRUNCATE accounting_account');
        $this->db->exec('TRUNCATE accounting_post');
        $this->db->exec('TRUNCATE accounting_year');
        $this->db->exec('TRUNCATE accounting_voucher');
    }

    function createKernel()
    {
        $kernel = new Stub_Kernel;
        $kernel->setting->set('user', 'accounting.active_year', '1');
        $kernel->setting->set('intranet', 'vatpercent', 25);
        return $kernel;
    }

    function createReminder()
    {
        return new Reminder($this->createKernel());
    }

    function createContact()
    {
        $contact = new Contact($this->createKernel());
        return $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
    }

    function createAnInvoice($contact_id)
    {
        $debtor = new Invoice($this->createKernel());
        $invoice_id = $debtor->update(
            array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'))
        );
        // maybe attach some items!
        return $invoice_id;
    }

    function createAccountingYear()
    {
        require_once 'Intraface/modules/accounting/Year.php';
        $year = new Year($this->createKernel());
        $year->save(array('from_date' => date('Y').'-01-01', 'to_date' => date('Y').'-12-31', 'label' => 'test', 'locked' => 0));
        $year->createAccounts('standard');
        return $year;
    }

    function testConstruct()
    {
        $reminder = $this->createReminder();
        $this->assertTrue(is_object($reminder));
    }

    function testSaveOnEmptyArray()
    {
        $reminder = $this->createReminder();
        $this->assertFalse($reminder->save(array()));
    }

    function testSaveWithValidData()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();

        $this->assertTrue($reminder->save(
            array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'checked_invoice' => array($this->createAnInvoice($contact_id)))
        ) > 0);
    }

    function testSetStatus()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'checked_invoice' => array($this->createAnInvoice($contact_id))));

        $this->assertTrue($reminder->setStatus('sent'));
    }

    function testDelete()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'checked_invoice' => array($this->createAnInvoice($contact_id))));

        $this->assertTrue($reminder->delete());

        /**
         * @todo: Reminder can be loaded despite it is deleted. Is that correct?
         */
        // $reminder = new Reminder($this->createKernel(), 1);
        // $this->assertEquals(0, $reminder->get('id'));
    }

    function testSomethingToStateWithoutReminderFee()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'checked_invoice' => array($this->createAnInvoice($contact_id))));

        $this->assertFalse($reminder->somethingToState($this->createAccountingYear()));
    }

    function testSomethingToStateWithReminderFee()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'reminder_fee' => 100,
                'checked_invoice' => array($this->createAnInvoice($contact_id))));

        $this->assertTrue($reminder->somethingToState($this->createAccountingYear()));
    }

    function testReadyForStateBeforeSent()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'reminder_fee' => 100,
                'checked_invoice' => array($this->createAnInvoice($contact_id))));

        $this->assertFalse($reminder->readyForState($this->createAccountingYear()), $reminder->error->view());
    }

    function testReadyForStateAfterSent()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'reminder_fee' => 100,
                'checked_invoice' => array($this->createAnInvoice($contact_id))));
        $reminder->setStatus('sent');

        $this->assertTrue($reminder->readyForState($this->createAccountingYear()), $reminder->error->view());
    }

    function testState()
    {
        $reminder = $this->createReminder();
        $contact_id = $this->createContact();
        $reminder->save(array(
                'number' => 1,
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'send_as' => 'pdf',
                'reminder_fee' => 100,
                'checked_invoice' => array($this->createAnInvoice($contact_id))));
        $reminder->setStatus('sent');
        $year = $this->createAccountingYear();
        $this->assertTrue($reminder->state($year, 1, '10-01-' . date('Y'), 1120, new Stub_Translation), $reminder->error->view());

        $voucher = Voucher::factory($year, 1);

        $expected = array(
            0 => array(
                'id' => 1,
                'date_dk' => '10-01-' . date('Y'),
                'date' => date('Y') . '-01-10',
                'text' => 'reminder #1',
                'debet' => 100.00,
                'credit' => 0.00,
                'voucher_number' => 1,
                'reference' => '',
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
                'text' => 'reminder #1',
                'debet' => 0.00,
                'credit' => 100.00,
                'voucher_number' => 1,
                'reference' => '',
                'voucher_id' => 1,
                'account_id' => 3,
                'stated' => 1,
                'account_number' => 1120,
                'account_name' => 'Salg uden moms'
            )
        );


        $this->assertEquals($expected, $voucher->getPosts());
        $this->assertTrue($reminder->isStated());
        $this->assertFalse($reminder->readyForState($year));
    }
}
