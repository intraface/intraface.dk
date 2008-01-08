<?php
class FakeKernel {
    public $intranet;
    public $user;
    public $setting;
    function useShared() {
        trigger_error('kernel->useShared should not be used in classes. Please rewrite the method', E_USER_ERROR);
        exit;
    }
    
    function useModule() {
        trigger_error('kernel->useModule should not be used in classes. Please rewrite the method!', E_USER_ERROR);
        exit;
    }
    
    function getModule() {
        trigger_error('kernel->getModule should not be used in classes. Please rewrite the method!', E_USER_ERROR);
        exit;
    }
    
    public function getSessionId() {
        return $this->session_id = 'notreallyauniquesessionid';
    }
}
?>
