<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/debtor/Debtor.php';
require_once 'Intraface/tools/Date.php';
require_once 'tests/unit/stubs/Translation.php';


class FakeDebtorAddress {
    function get($key = '') {
        $info = array('name' => 'Lars Olesen', 'address' => 'Græsvangen 8, Syvsten', 'postcode' => 9300, 'city' => 'Aarhus N', 'cvr' => '', 'ean' => '', 'phone' => '75820811', 'email' => 'lars@legestue.net', 'address_id' => 1);
        if (empty($key)) return $info;
        else return $info[$key];
    }
}

class FakeDebtorUser
{
    function hasModuleAccess()
    {
        return true;
    }
    function get(){
        return 1;
    }
}

class FakeDebtorIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeDebtorAddress;
    }
    function get($key = '') {
        $info = array('name' => 'Intranetname', 'contact_person' => '','id' => 1);
        if (empty($key)) return $info;
        else return $info[$key];
    }
    
    function hasModuleAccess() {
        return true;
    }
}

class FakeDebtorSetting {
    
    function get($type, $setting) {
        
        $info = array('intranet' => array('onlinepayment.provider_key' => 1));
        
        return $info[$type][$setting];
    }
    
}

class DebtorTest extends PHPUnit_Framework_TestCase
{
    private $kernel;
    
    function setUp() {
        
        $db = MDB2::factory(DB_DSN);
        $db->query('TRUNCATE debtor');
        $db->query('TRUNCATE debtor_item');
        
        
        
        $kernel = new Kernel;
        $kernel->user = new FakeDebtorUser;
        $kernel->intranet = new FakeDebtorIntranet;
        $kernel->setting = new FakeDebtorSetting;
        $kernel->useModule('debtor');
        $this->kernel = $kernel;
    }
    
    function createDebtor()
    {
        
        return new Debtor($this->kernel, 'order');
    }
    
    function createContact() {
        $this->kernel->useModule('contact');
        $contact = new Contact($this->kernel);
        
        return $contact->save(array('name' => 'Test', 'email' => 'lars@legestue.net', 'phone' => '98468269'));
    }

    function testConstruct()
    {
        $debtor = $this->createDebtor();
        $this->assertTrue(is_object($debtor));
    }
    
    
    
    function testUpdate() {
        $debtor = $this->createDebtor();
        
        
        $this->assertTrue($debtor->update(
            array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y'))
            ) > 0);
    }
    
    function testSetStatus() {
        
        $debtor = $this->createDebtor();
        
        $debtor->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        
        $this->assertTrue($debtor->setStatus('sent'));
    }
    
    function testCreate() {
        
        $quotation = new Debtor($this->kernel, 'quotation');
        
        $quotation->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
       
        $order = new Debtor($this->kernel, 'order');
        $this->assertTrue($order->create($quotation) > 0);
    }
    
    function testDelete() {
        $debtor = $this->createDebtor();
        
        $debtor->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        
        $this->assertTrue($debtor->delete());
    }
    
    function testAnyWithContact() {
        
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
    
    function testGetMaxNumber() {
        
        $debtor = $this->createDebtor();
        
        $debtor->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')));
        
        
        
        $this->assertEquals($debtor->get('number'), $debtor->getMaxNumber());
    }
    
    function testSetFrom() {
        $debtor = $this->createDebtor();
        $this->assertEquals(1, $debtor->update(array(
                'contact_id' => $this->createContact(), 
                'description' =>'test',
                'this_date' => date('d-m-Y'),
                'due_date' => date('d-m-Y')), 'quotation'));
    }
}

?>