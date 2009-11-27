<?php
class Stub_Intranet
{
    public $address;

    function __construct()
    {
        $this->address = new Stub_Address;
    }

    function get($key = '')
    {
        $info = array('name' => 'Intranetname', 'contact_person' => '','id' => 1, 'public_key' => 'somepublickey', 'identifier' => 'intraface');
        if (empty($key)) return $info;
        else return $info[$key];
    }

    function hasModuleAccess()
    {
        return true;
    }

    function getId()
    {
        return 1;
    }
}
