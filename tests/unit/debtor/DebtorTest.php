<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/debtor/Debtor.php';


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

class DebtorTest extends PHPUnit_Framework_TestCase
{
    function createDebtor()
    {
        $kernel = new Kernel;
        $kernel->user = new FakeDebtorUser;
        $kernel->intranet = new FakeDebtorIntranet;
        $kernel->useModule('debtor');
        return new Debtor($kernel, 'order');
    }

    function testConstruct()
    {
        $debtor = $this->createDebtor();
        $this->assertTrue(is_object($debtor));
    }
}

?>