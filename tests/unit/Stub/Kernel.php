<?php
class Stub_Kernel
{
    public $intranet;
    public $user;
    public $setting;
    public $session_id;

    function __construct()
    {
        $this->intranet = new Stub_Intranet;
        $this->user = new Stub_User;
        $this->setting = new Stub_Setting;
    }

    function useShared()
    {
        throw new Exception('kernel->useShared should not be used in classes. Please rewrite the method');
    }

    function module()
    {
        throw new Exception('kernel->module should not be used in classes. Please rewrite the method!');
    }

    function useModule()
    {
        throw new Exception('kernel->useModule should not be used in classes. Please rewrite the method!');
    }

    function getModule()
    {
        throw new Exception('kernel->getModule should not be used in classes. Please rewrite the method!');
    }

    public function getSessionId()
    {
        return $this->session_id = 'notreallyauniquesessionid';
    }

    function getSetting()
    {
        return $this->setting;
    }
}
