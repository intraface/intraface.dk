<?php
class FakeContact
{
    
    function __construct()
    {
        $this->values = array(
            'name' => 'Contact Name',
            'id' => 1,
            'number' => 1
        );
    }
    
    
    function get($key)
    {
        return $this->values[$key];
    }
}
