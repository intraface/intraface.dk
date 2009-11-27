<?php
class Stub_Kernel extends Intraface_Kernel
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
        $this->setting->set('intranet', 'contact.login_url', 'http://localhost/');
        $this->setting->set('intranet', 'webshop.confirmation_text', 'sometext');
        $this->setting->set('intranet', 'webshop.show_online', true);
        $this->setting->set('intranet', 'contact.login_email_text', 'sometext');
        $this->setting->set('intranet', 'cms.stylesheet.site', 'something');
        $this->session_id = 'notreallyauniquesessionid';
    }
    /*
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
    */

    public function getSessionId()
    {
        return $this->session_id = 'notreallyauniquesessionid';
    }

    function getSetting()
    {
        return $this->setting;
    }
}
