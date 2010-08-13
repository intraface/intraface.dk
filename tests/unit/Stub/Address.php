<?php
class Stub_Address
{
    function get($key = '')
    {
        $info = array('name' => 'Lars Olesen', 'address' => 'Græsvangen 8, Syvsten', 'postcode' => 9300, 'city' => 'Aarhus N', 'country' => 'Danmark', 'cvr' => '123456789', 'ean' => '', 'phone' => '75820811', 'email' => 'lars@legestue.net', 'address_id' => 1);
        if (empty($key)) return $info;
        else return $info[$key];
    }
}
