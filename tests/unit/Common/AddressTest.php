<?php
require_once 'Intraface/Error.php';
require_once 'Intraface/Validator.php';
require_once 'Intraface/Address.php';

class AddressTest extends PHPUnit_Framework_TestCase
{

    private $kernel;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE address');
    }

    function getValidAddress()
    {
        return array('name' => 'test', 'address' => 'road 1', 'postcode' => '0123', 'city' => 'Mycity', 'country' => '', 'email' => 'sj@sunet.dk', 'cvr' => '', 'website' => '', 'phone' => '', 'ean' => '');
    }

    function testValidateWithValidAddress()
    {

        $address = Intraface_Address::factory('intranet', 1);
        $this->assertTrue($address->validate($this->getValidAddress()));
    }

    function testValidateWithInvalidAddress()
    {
        $address = Intraface_Address::factory('intranet', 1);
        $this->assertFalse($address->validate(array()));
    }

    function testSaveOnEmptyDB()
    {
        $address = Intraface_Address::factory('intranet', 1);
        $this->assertTrue($address->save($this->getValidAddress()));
        $this->assertEquals(1, $address->get('address_id')); // on empty database this must be 1
    }

    function testSaveWithSaveTwoTimeWithSameData()
    {
        $address = Intraface_Address::factory('intranet', 1);
        $address->save($this->getValidAddress());


        // we repeat the save, this should automatically determine that nothing should be done. Can we determine whether this is true?
        $this->assertTrue($address->save($this->getValidAddress()));
        $this->assertEquals(1, $address->get('address_id')); // on empty database this must be 1
    }

    function testSaveWithChangeInName()
    {
        $address = Intraface_Address::factory('intranet', 1);

        $address_array = $this->getValidAddress();

        $address = Intraface_Address::factory('intranet', 1);
        $address->save($address_array);

        $address_array['name'] = 'test 2';
        $this->assertTrue($address->save($address_array));
        $this->assertEquals(2, $address->get('address_id')); // on empty database this must be 1
    }

    function testUpdate()
    {
        /*
        Da metoden er fjernet fra klassen er testen ogs� fjernet.
        $address = Address::factory('intranet', 1);
        $address->save($this->getValidAddress());
        $this->assertTrue($address->update($this->getValidAddress()));
        $this->assertEquals(1, $address->get('address_id')); // on empty database this must be 1
        */
    }

    function testLoad()
    {
        $address = Intraface_Address::factory('intranet', 1);
        $address->save($this->getValidAddress());

        $address = new Intraface_Address(1);
        $this->assertEquals(1, $address->get('address_id'));
    }
}
