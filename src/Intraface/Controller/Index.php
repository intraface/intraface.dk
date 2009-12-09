<?php
class Intraface_Controller_Index extends k_Component
{
    protected $kernel_gateway;
    protected $user_gateway;
    protected $template;

    function __construct(k_TemplateFactory $template, Intraface_Auth $auth, Intraface_KernelGateway $gateway, Intraface_UserGateway $user_gateway)
    {
        $this->kernel_gateway = $gateway;
        $this->user_gateway = $user_gateway;
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'logout') { // skal sikkert være fra restricted controller i stedet
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
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('restricted'));
        /*
        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
        */
    }

    function getKernel()
    {
        return $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/outside');
        $content = $tpl->render($this, array('content' => $content));
        return new k_HttpResponse(200, $content, true);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function document()
    {
        return $this->document;
    }
}