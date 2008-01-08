<?php

require_once 'Address.php';

class FakeIntranet
{
    public $address;
    function __construct() {
        $this->address = new FakeAddress;
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
?>
