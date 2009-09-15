<?php
class Intraface_Controller_Index extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'logout') { // skal sikkert være fra restricted controller i stedet
            return 'Intraface_Controller_Logout';
        } elseif ($name == 'login') {
            return 'Intraface_Controller_Login';
        } elseif ($name == 'retrievepassword') {
            return 'Intraface_Controller_RetrievePassword';
        } elseif ($name == 'restricted') {
            return 'Intraface_Controller_Restricted';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $smarty->render($this);
    }

    function GET()
    {
    	if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
			$this->getKernel()->setting->set('user', 'homepage.message', 'hide');
		}
    	return parent::GET();
    }

    function getKernel()
    {
        $registry = $this->registry->create();
    	return $registry->get('kernel');
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }

    function getTranslation()
    {
    	return $this->getKernel()->getTranslation();
    }

    function getLastView()
    {
		$last_view = $this->getKernel()->setting->get('user', 'homepage.last_view');
		$this->getKernel()->setting->set('user', 'homepage.last_view', date('Y-m-d H:i:s'));
    	return $last_view;
    }

    function getAuth()
    {
        return new Intraface_Auth(session_id());
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