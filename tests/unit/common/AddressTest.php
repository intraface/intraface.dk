<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Address.php';

class AddressTest extends PHPUnit_Framework_TestCase
{
    function createAddress($id = 0)
    {
        return new Address($id);
    }

    function testConstruction()
    {
        $address = $this->createAddress();
        $this->assertTrue(is_object($address));
    }

    function testFactory()
    {
        $address = Address::factory('contact', 1);
        $this->assertTrue(is_object($address));
    }

}
?>