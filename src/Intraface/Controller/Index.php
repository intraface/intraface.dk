<?php
class Intraface_Controller_Index extends k_Component
{
    protected $kernel_gateway;
    protected $user_gateway;
    protected $template;
    protected $auth;

    function __construct(k_TemplateFactory $template, Intraface_Auth $auth, Intraface_KernelGateway $gateway, Intraface_UserGateway $user_gateway)
    {
        $this->kernel_gateway = $gateway;
        $this->user_gateway = $user_gateway;
        $this->template = $template;
        $this->auth = $auth;
    }

    protected function map($name)
    {
        if ($name == 'logout') { // @todo Maybe move to a restricted controller instead
            return 'Intraface_Controller_Logout';
        } elseif ($name == 'login') {
            return 'Intraface_Controller_Login';
        } elseif ($name == 'testlogin') {
            return 'Intraface_Controller_TestLogin';
        } elseif ($name == 'retrievepassword') {
            return 'Intraface_Controller_RetrievePassword';
        } elseif ($name == 'restricted') {
            return 'Intraface_Controller_Restricted';
        } elseif ($name == 'signup') {
            return 'Intraface_Controller_Signup';
        } elseif ($name == 'payment') {
            return 'Intraface_Controller_Payment';
        } elseif ($name == 'file') {
            return 'Intraface_Filehandler_Controller_Viewer';
        } elseif ($name == 'process') {
            return 'Intraface_Controller_ModulePackage_Process';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('restricted'));
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/outside');
        $content = $tpl->render($this, array('content' => $content));
        return new k_HtmlResponse($content);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function getKernel()
    {
        return $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }
}
