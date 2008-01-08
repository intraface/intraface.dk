<?php
class FakeDebtor
{
    
    
    
    function get($key) {
        $values = array('id' => 1, 'locked' => 0, 'type' => 'invoice');
        return $values[$key];
    }
}
?>
