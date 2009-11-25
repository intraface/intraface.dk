<?php
class Intraface_Controller_Index extends k_Component
{
    protected $registry;
    protected $kernel_gateway;
    protected $user_gateway;

    function __construct(k_Registry $registry, Intraface_KernelGateway $gateway, Intraface_UserGateway $user_gateway)
    {
        $this->registry = $registry;
        $this->kernel_gateway = $gateway;
        $this->user_gateway = $user_gateway;
    }

    protected function map($name)
    {
        if ($name == 'logout') { // skal sikkert v�re fra restricted controller i stedet
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

    function getTranslation()
    {
    	return $this->getKernel()->getTranslation();
    }

    function getAuth()
    {
        return new Intraface_Auth(session_id());
    }

    function t($phrase)
    {
        return $phrase;
    }

    function wrapHtml($content)
    {
        ob_start();
        include dirname(__FILE__) . '/../ihtml/outside/top.php';
        $header = ob_get_contents();
        ob_end_clean();
        ob_start();
        include dirname(__FILE__) . '/../ihtml/outside/bottom.php';
        $footer = ob_get_contents();
        ob_end_clean();
        return new k_HttpResponse(200, $header . $content . $footer);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }
}