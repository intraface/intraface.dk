<?php
class Intraface_modules_intranetmaintenance_Controller_User_Show extends k_Component
{
    protected $registry;
    protected $user;
    public $method = 'put';
    protected $intranetmaintenance;

    protected function map($name)
    {
        if ($name == 'permission') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Permission';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $user_id = intval($this->name());

        if (isset($_GET['return_redirect_id'])) {
            if (isset($_GET['intranet_id'])) {
                $intranet = new IntranetMaintenance($_GET['intranet_id']);
                $edit_intranet_id = $intranet->get('id');
            }
            $redirect = Intraface_Redirect::factory($kernel, 'return');
            if ($redirect->get('identifier') == 'add_user') {
                $user = new UserMaintenance($redirect->getParameter('user_id'));
                $user->setIntranetAccess($intranet->get('id'));
                $user_id = $user->get('id');
            }
        }

        if (isset($edit_intranet_id)) {
            $intranet = new IntranetMaintenance(intval($edit_intranet_id));
            $user->setIntranetId(intval($intranet->get('id')));
            $address = $user->getAddress();
            if (isset($address)) {
                $value_address = $user->getAddress()->get();
            }
        }

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/user/show.tpl.php');
        return $smarty->render($this);
    }

    function putForm()
    {
        $module = $this->getKernel()->module("intranetmaintenance");

        $user = new UserMaintenance(intval($this->name()));

        if (isset($_POST["intranet_id"]) && intval($_POST["intranet_id"]) != 0) {
            $intranet = new Intraface_Intranet($_POST["intranet_id"]);
            $intranet_id = $intranet->get("id");
            $address_value = $_POST;
            $address_value["name"] = $_POST["address_name"];
        } else {
            $intranet_id = 0;
            $address_value = array();
        }

        $value = $_POST;

        if ($user->update($_POST)) {
            if (isset($intranet)) {
                $user->setIntranetAccess($intranet->get('id'));
                $user->setIntranetId($intranet->get('id'));
                $user->getAddress()->save($address_value);

                    return new k_SeeOther($this->url(null, array('intranet_id' => $intranet->get("id"))));

            } else {
                return new k_SeeOther($this->url(null));
            }
        }
        return $this->render();
    }

    function getValues()
    {
        $user = $this->getUser();
        $value = $user->get();
        if (isset($_REQUEST['intranet_id'])) {
            $intranet_id = intval($_REQUEST["intranet_id"]);
            $user->setIntranetId($intranet_id);
            $address_value = $user->getAddress()->get();
        } else {
            $intranet_id = 0;
            $address_value = array();
        }

        return array_merge($address_value, $value);
    }

    function renderVcard()
    {
        // instantiate a builder object
        // (defaults to version 3.0)
        $vcard = new Contact_Vcard_Build();

        // set a formatted name
        $vcard->setFormattedName($this->getUser()->get('name'));

        // set the structured name parts
        // @todo adskille navnet i fornavn og efternavn
        $vcard->setName($this->getUser()->get('name'), $this->getUser()->get('name'), '', '', '');

        // add phone
        $vcard->addTelephone($this->getUser()->getAddress()->get('phone'));
        $vcard->addParam('TYPE', 'HOME');
        $vcard->addParam('TYPE', 'PREF');

        // add a home/preferred email
        $vcard->addEmail($this->getUser()->getAddress()->get('email'));
        $vcard->addParam('TYPE', 'HOME');
        $vcard->addParam('TYPE', 'PREF');

        // add a work address
        $vcard->addAddress('', '', $this->getUser()->getAddress()->get('address'), $this->getUser()->getAddress()->get('city'), '', $this->getUser()->getAddress()->get('postcode'), $this->getUser()->getAddress()->get('country'));
        $vcard->addParam('TYPE', 'WORK');

        // get back the vCard and print it
        return $vcard->fetch();
    }

    function _getValues()
    {
        $user = new UserMaintenance($this->name());

        $value = $user->get();
        $value_address = array();

        if (isset($_GET['intranet_id'])) {
            $edit_intranet_id = intval($_GET['intranet_id']);
        }

        if (isset($edit_intranet_id)) {
            $intranet = new IntranetMaintenance(intval($edit_intranet_id));
            $user->setIntranetId(intval($intranet->get('id')));
            $address = $user->getAddress();
            if (isset($address)) {
                $value_address = $user->getAddress()->get();
            }
        }
        return array_merge($value, $value_address);
    }

    function getUser()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        if (is_object($this->user)) {
            return $this->user;
        }
        $this->user = new UserMaintenance($this->name());
        $this->user->setIntranetId($this->getIntranet()->get('id'));
        return ($this->user);
    }

    function getIntranet()
    {
        if (is_object($this->intranetmaintenance)) {
            return $this->intranetmaintenance;
        }
        if (method_exists($this->context, 'getIntranet') AND $this->context->getIntranet()->getId() > 0) {
            return $this->intranetmaintenance = new IntranetMaintenance($this->context->getIntranet()->getId());
        }

        return $this->intranetmaintenance = new IntranetMaintenance();
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

    function postForm()
    {
        $module = $this->getKernel()->module("intranetmaintenance");

        $user = new UserMaintenance(intval($this->name()));
        $intranet = new IntranetMaintenance(intval($_POST["intranet_id"]));
        $user->setIntranetId($intranet->get("id"));

        $modules = array();
        if (isset($_POST['module'])) {
            $modules = $_POST["module"];
        } else {
            $modules = array();
        }
        if (isset($_POST['sub_access'])) {
            $sub_access = $_POST["sub_access"];
        } else {
            $sub_access = array();
        }

        $user->flushAccess();

        if (!isset($_POST["intranetaccess"])) {
            // Access to intranet is not set. We show the user, but not with the intranet.
            $user_id = intval($this->name());
            unset($user);
            unset($intranet);
        } else {
            // Sætter adgang til det redigerede intranet. Id kommer tidligere ved setIntranetId
            $user->setIntranetAccess();

            // Hvis en bruger retter sig selv, i det aktive intranet, sætter vi adgang til dette modul
            if ($this->getKernel()->user->get("id") == $user->get("id") && $this->getKernel()->intranet->get("id") == $intranet->get("id")) {
                // Finder det aktive intranet
                $active_module = $this->getKernel()->getPrimaryModule();
                // Giver adgang til det
                $user->setModuleAccess($active_module->getId());
            }

            for ($i = 0, $max = count($modules); $i < $max; $i++) {
                $user->setModuleAccess($modules[$i]);
                if (!empty($sub_access[$modules[$i]])) {
                    for ($j = 0, $max1 = count($sub_access[$modules[$i]]); $j < $max1; $j++) {
                        $user->setSubAccess($modules[$i], $sub_access[$modules[$i]][$j]);
                    }
                }
            }
            $user_id = $user->get('id');
            $edit_intranet_id = $intranet->get('id');

            return new k_SeeOther($this->url(null, array('intranet_id' => $edit_intranet_id)));
        }
    }

    function renderHtmlEdit()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/user/edit.tpl.php');
        return $smarty->render($this);
    }

    function getIntranets()
    {
        $intranet = new IntranetMaintenance();
        $intranet->getDBQuery($this->getKernel())->setFilter('user_id', $this->getUser()->get('id'));

        return $intranet->getList();
    }

    function getModules()
    {
        $module = new ModuleMaintenance;
        return $module->getList();
    }
}
