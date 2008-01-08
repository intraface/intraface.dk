<?php
class FakeSetting {
    
    function get($type, $setting) {
        
        $info = array(
            'intranet' => array('onlinepayment.provider_key' => 1));
        
        if(!isset($info[$type][$setting])) {
            trigger_error('You need to create the setting '.$type.':'.$setting.' in stubs/Setting.php', E_USER_ERROR);
            exit;
        }
        
        return $info[$type][$setting];
    }
    
}
?>
