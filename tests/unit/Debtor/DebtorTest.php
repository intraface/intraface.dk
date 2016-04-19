<?php
require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/debtor/Debtor.php';
require_once 'Intraface/Date.php';

Intraface_Doctrine_Intranet::singleton(1);

class FakeDebtorAddress
{
    function get($key = '')
    {
        $info = array('name' => 'Lars Olesen', 'address' => 'Gr�svangen 8, Syvsten', 'postcode' => 9300, 'city' => 'Aarhus N', 'cvr' => '', 'ean' => '', 'phone' => '75820811', 'email' => 'lars@legestue.net', 'address_id' => 1);
        if (empty($key)) {
            return $info;
        } else {
            return $info[$key];
        }
    }
}

class FakeDebtorUser
{
    function hasModuleAccess()
    {
        return true;
    }
    function get()
    {
        return 1;
    }
}

class FakeDebtorIntranet
{
    public $address;
    function __construct()
    {
        $this->address = new FakeDebtorAddress;
    }
    function get($key = '')
    {
        $info = array('name' => 'Intranetname', 'contact_person' => '','id' => 1);
        if (empty($key)) {
            return $info;
        } else {
            return $info[$key];
        }
    }

    function getId()
    {
        return 1;
    }

    function hasModuleAccess()
    {
        return true;
    }
}

class FakeDebtorSetting
{

    function get($type, $setting)
    {

        $info = array('intranet' => array('onlinepayment.provider_key' => 1));

        return $info[$type][$setting];
    }
}

class DebtorTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    protected $db;

    function setUp()
    {

        $this->db = MDB2::singleton(DB_DSN);

        $kernel = new Intraface_Kernel;
        $kernel->user = new FakeDebtorUser;
        $kernel->intranet = new FakeDebtorIntranet;
        $kernel->setting = new FakeDebtorSetting;
        $kernel->useModule('debtor');
        $this->kernel = $kernel;
    }

    function tearDown()
    {
        $this->db->query('TRUNCATE debtor');
        $this->db->query('TRUNCATE debtor_item');
        $this->db->query('TRUNCATE currency');
        $this->db->query('TRUNCATE currency_exchangerate');

        $this->db->query('TRUNCATE product');
        $this->db->query('TRUNCATE product_detail');
        $this->db->query('TRUNCATE product_detail_translation');

        $this->db->query('TRUNCATE invoice_payment');
    }

    function createDebtor()
    {

        return new Debtor($this->kernel, 'order');
    }

    function createContact()
    {
        $this->kernel->useModule('contact');
        $contact = new Contact($this->kernel);

        return $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
    }

    function createProduct()
    {
        $this->kernel->useModule('product');
        $product = new Product($this->kernel);

        return $product->save(array('name' => 'Test', 'price' => 20, 'unit' => 1));
    }

    function createCurrency()
    {
        $currency = new Intraface_modules_currency_Currency;
        $currency->setType(new Intraface_modules_currency_Currency_Type_Eur);
        try {
            $currency->save();
        } catch (Exception $e) {
            print_r($currency->getErrorStack());
            die;
        }
        $excr = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $excr->setRate(new Ilib_Variable_Float(745.23));
        $excr->setCurrency($currency);
        $excr->save();

        return $currency;

    }

    function createPayment($debtor)
    {
        $payment = new Payment($debtor);
        return $payment->update(array('payment_date' => '01-01-2007', 'amount' => 50, 'type' => 1));
    }

    function testConstruct()
    {
        $debtor = $this->createDebtor();
        $this->assertTrue(is_object($debtor));
    }

    function testGetDBQuery()
    {
        $debtor = $this->createDebtor();
        $this->assertEquals('Intraface_DBQuery', get_class($debtor->getDBQuery()));
    }

    function testFactoryWitnIdentifier()
    {
        $debtor = $this->createDebtor();
        $description = 'test debtor';
        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' => $description,
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $debtor = Debtor::factory($this->kernel, $debtor->get('identifier_key'));

        $this->assertEquals('Order', get_class($debtor));
        $this->assertEquals($description, $debtor->get('description'));


    }


    function testUpdate()
    {
        $debtor = $this->createDebtor();


        $this->assertTrue($debtor->update(
            array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'))
        ) > 0);
    }

    function testUpdateWithCurrency()
    {
        $debtor = $this->createDebtor();

        $currency = new Intraface_modules_currency_Currency;
        $currency->setType(new Intraface_modules_currency_Currency_Type_Eur);
        try {
            $currency->save();
        } catch (Exception $e) {
            print_r($currency->getErrorStack());
            die;
        }
        $excr = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $excr->setRate(new Ilib_Variable_Float(745.23));
        $excr->setCurrency($currency);
        $excr->save();

        $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection(DB_DSN));

        $this->assertTrue($debtor->update(
            array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'currency' => $currency)
        ) > 0);

    }

    function testGetCurrency()
    {
        $debtor = $this->createDebtor();

        $debtor->update(
            array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'currency' => $this->createCurrency())
        );
        $debtor->load();

        $this->assertEquals('Intraface_modules_currency_Currency', get_class($debtor->getCurrency()));

    }

    function testSetStatus()
    {

        $debtor = $this->createDebtor();

        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $this->assertTrue($debtor->setStatus('sent'));
    }

    function testGetStatusAfterStatusChange()
    {

        $debtor = $this->createDebtor();

        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $debtor->setStatus('sent');
        $debtor->load();
        $this->assertEquals('sent', $debtor->get('status'));
    }

    function testSetNewContact()
    {
        $debtor = $this->createDebtor();
        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        $this->assertTrue($debtor->setNewContact($this->createContact()));
    }

    function testCreate()
    {

        $quotation = new Debtor($this->kernel, 'quotation');

        $quotation->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $order = new Debtor($this->kernel, 'order');
        $this->assertTrue($order->create($quotation) > 0);
    }

    function testCreateWithCurrency()
    {

        $quotation = new Debtor($this->kernel, 'quotation');

        $quotation->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'currency' => $this->createCurrency()));

        $order = new Debtor($this->kernel, 'order');
        $this->assertTrue($order->create($quotation) > 0);
        $this->assertEquals('Intraface_modules_currency_Currency', get_class($order->getCurrency()));
    }

    function testDelete()
    {
        $debtor = $this->createDebtor();

        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $this->assertTrue($debtor->delete());
    }

    function testAnyWithContact()
    {

        // we make sure there is a debtor first

        $debtor = $this->createDebtor();
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $this->assertTrue($debtor->any('contact', $contact_id) > 0);
    }

    function testAnyWithProduct()
    {

        $debtor = $this->createDebtor();
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));

        $product_id = $this->createProduct();
        $debtor->loadItem();
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 1));

        $this->assertTrue($debtor->any('product', $product_id) > 0);
    }

    function testGetTotal()
    {
        $debtor = $this->createDebtor();
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        $product_id = $this->createProduct();
        $debtor->loadItem();
        $this->assertEquals(1, $product_id);
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3));
        $debtor->load();

        $this->assertEquals(75, $debtor->getTotal()->getAsIso());
    }

    function testGetTotalInCurrency()
    {
        $debtor = $this->createDebtor();
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'currency' => $this->createCurrency()));
        $product_id = $this->createProduct();
        $debtor->loadItem();
        $this->assertEquals(1, $product_id);
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3));
        $debtor->load();

        $this->assertEquals(10.05, $debtor->getTotalInCurrency()->getAsIso());

    }

    function testGetArrears()
    {
        require_once 'Intraface/modules/invoice/Invoice.php';
        $debtor = new Invoice($this->kernel);
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        $product_id = $this->createProduct();
        $debtor->loadItem();
        $this->assertEquals(1, $product_id);
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3));

        $this->createPayment($debtor);

        $debtor->load();

        $this->assertEquals(75, $debtor->getTotal()->getAsIso());
        $this->assertEquals(25, $debtor->getArrears()->getAsIso());
    }

    function testGetArrearsInCurrency()
    {
        $debtor = new Invoice($this->kernel);
        $contact_id = $this->createContact();
        $debtor->update(array(
                'contact_id' => $contact_id,
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'),
                'currency' => $this->createCurrency()));
        $product_id = $this->createProduct();
        $debtor->loadItem();
        $this->assertEquals(1, $product_id);
        $debtor->item->save(array('product_id' => $product_id, 'quantity' => 3));

        $this->createPayment($debtor);

        $debtor->load();

        $this->assertEquals(75, $debtor->getTotal()->getAsIso());
        $this->assertEquals(3.35, $debtor->getArrearsInCurrency()->getAsIso());
    }

    function testGetMaxNumber()
    {

        $debtor = $this->createDebtor();

        $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));



        $this->assertEquals($debtor->get('number'), $debtor->getMaxNumber());
    }

    function testSetFrom()
    {
        $debtor = $this->createDebtor();
        $this->assertEquals(1, $debtor->update(array(
                'contact_id' => $this->createContact(),
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')), 'quotation'));
    }
}
