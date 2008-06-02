<?php

class FakeIntranet
{
    
    function __construct() {
        
    }
    function get($key = '') {
        $info = array('name' => 'Intranetname', 'contact_person' => '','id' => 1);
        if (empty($key)) return $info;
        else return $info[$key];
    }
    
    function hasModuleAccess() {
        return true;
    }
    
    function getId() {
        return 1;
    }    
}
?>
