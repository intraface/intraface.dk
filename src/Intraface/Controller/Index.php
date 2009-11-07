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
}

/*

// Adds link for id user details is filled in. They are going to be in the top.
if (!$kernel->user->isFilledIn()) {
	$_advice[] = array(
		'msg' => 'all information about you has not been filled in',
		'link' => url('/main/controlpanel/user_edit.php'),
		'module' => 'dashboard'
	);
}

// getting stuff to show on the dashboard


for ($i = 0, $max = count($modules); $i < $max; $i++) {

	if (!$kernel->intranet->hasModuleAccess(intval($modules[$i]['id']))) {
		continue;
	}
	if (!$kernel->user->hasModuleAccess(intval($modules[$i]['id']))) {
		continue;
	}

	$module = $kernel->useModule($modules[$i]['name']);
	$frontpage_files = $module->getFrontpageFiles();

	if (!is_array($frontpage_files) OR count($frontpage_files) == 0) {
		continue;
	}

	foreach ($frontpage_files AS $file) {
		$file = PATH_INCLUDE_MODULE . $module->getName() . '/' .$file;
		if (file_exists($file)) {
			include($file);
		}
	}
}
*/