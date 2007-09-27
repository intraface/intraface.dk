<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/debtor/DebtorItem.php';


class FakeAddress {
    function get($key = '') {
        $info = array('name' => 'Lars Olesen', 'address' => 'Græsvangen 8, Syvsten', 'postcode' => 9300, 'city' => 'Aarhus N', 'cvr' => '', 'ean' => '', 'phone' => '75820811', 'email' => 'lars@legestue.net');
        if (empty($key)) return $info;
        else return $info[$key];
    }
}

class FakeContactPerson {
    function get() {}
}

class FakeContact
{
    public $address;
    function __construct()
    {
        $this->address = new FakeAddress;
    }
    function get() {
        return 'Contact Name';
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
        $this->address = new FakeAddress;
    }
    function get() {
        return array('name' => 'Intranetname', 'contact_person' => '');
    }
}

class FakeItemDebtor
{

}

class DebtorItemTest extends PHPUnit_Framework_TestCase
{
    function createDebtor()
    {
        $debtor = new FakeItemDebtor;
        return new DebtorItem($debtor);
    }

    function testConstruct()
    {
        $debtor = $this->createDebtor();
        $this->assertTrue(is_object($debtor));
    }
}

?>