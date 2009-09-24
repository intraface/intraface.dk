<?php
class Intraface_modules_intranetmaintenance_Controller_Intranet_Show extends k_Component
{
    protected $registry;
    protected $intranetmaintenance;
    public $method = 'put';
    public $error;

    protected function map($name)
    {
        if ($name == 'user') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Index';
        } elseif ($name == 'permission') {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Permission';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle('Intranet');

        $modul = $this->getKernel()->module("intranetmaintenance");
        try {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');
            }
        } catch (Exception $e) {
            $this->error = '<p>Kontaktmodulet findes ikke. Du har formentlig ikke registreret modulerne endnu. <a href="http://localhost/intraface/intraface/trunk/src/intraface.dk/modules/intranetmaintenance/modules.php?do=register">Registrer modulerne.</a></p>';
        }
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $intranet = new IntranetMaintenance($this->name());

        // add contact
        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($kernel, 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $modul->getPath()."intranet.php?id=".$intranet->get('id'));
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                return new k_SeeOther($url);
            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        }

        // add existing user
        if (isset($_GET['add_user']) && $_GET['add_user'] == 1) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('../../user'), NET_SCHEME . NET_HOST . $this->url(null));
            $redirect->askParameter('user_id');
            $redirect->setIdentifier('add_user');
            return new k_SeeOther($url);
        }

        // return
        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                $intranet->setContact($redirect->getParameter('contact_id'));
            }
        }

        if (isset($_GET['delete_intranet_module_package_id']) && (int)$_GET['delete_intranet_module_package_id'] != 0) {

            $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
            $modulepackagemanager->delete((int)$_GET['delete_intranet_module_package_id']);
        }

        $user = new UserMaintenance();
        $user->setIntranetId($intranet->get('id'));

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/intranet/show.tpl.php');
        return $smarty->render($this);
    }

    function getValues()
    {
        $intranet = new IntranetMaintenance($this->name());
        $value = $intranet->get();
        if (isset($intranet->address)) {
        	$address_value = $intranet->address->get();
        } else {
        	$address_value = array();
        }

        return array_merge($value, $address_value);
    }

    function getUsers()
    {
        $user = new UserMaintenance();
        $user->setIntranetId($this->getIntranet()->getId());
        return $user->getList($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getIntranet()
    {
        return new IntranetMaintenance($this->name());
    }

    /*
    function POST()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $intranet = new IntranetMaintenance(intval($this->name()));

        if (isset($_POST['add_module_package']) && $_POST['add_module_package'] != '') {
            $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
            $modulepackagemanager->save($_POST['module_package_id'], $_POST['start_date'], $_POST['duration_month'].' month');
        }

        // Update permission
        if (isset($_POST["change_permission"])) {

            $modules = array();
            $modules = $_POST["module"];

            $intranet->flushAccess();

            // Hvis man er i det samme intranet som man redigere
            if ($this->getKernel()->intranet->get("id") == $intranet->get("id")) {
                // Finder det aktive modul
                $active_module = $this->getKernel()->getPrimaryModule();
                // Giver adgang til det
                $intranet->setModuleAccess($active_module->getId());
            }

            for ($i = 0, $max = count($modules); $i < $max; $i++) {
                $intranet->setModuleAccess($modules[$i]);
            }

            return new k_SeeOther($this->url());
        }
    }
    */

    function renderHtmlEdit()
    {
        $this->document->setTitle('Edit intranet');

        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/intranet/edit.tpl.php');
        return $smarty->render($this);
    }

    function putForm()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');
        $intranet = new IntranetMaintenance(intval($_POST["id"]));

    	$value = $_POST;
    	$address_value = $_POST;
    	$address_value["name"] = $_POST["address_name"];

    	if ($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $this->getKernel()->intranet->get('id'))) {
    		if ($intranet->address->save($address_value)) {
    			return new k_SeeOther($this->url(null));
    		}
    	}
    	return $this->renderHtmlEdit();
    }
}


