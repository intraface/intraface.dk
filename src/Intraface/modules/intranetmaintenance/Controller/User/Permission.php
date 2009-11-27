<?php
class Intraface_modules_intranetmaintenance_Controller_User_Permission extends k_Component
{
    protected $registry;
    protected $user;
    public $method = 'put';

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/user/permission.tpl.php');
        return $smarty->render($this);
    }

    function getUser()
    {
        return ($this->context->getUser());
    }

    function getIntranet()
    {
        return $this->context->getIntranet();
    }

    function postForm()
    {
        $module = $this->getKernel()->module("intranetmaintenance");

        $user = $this->getUser();
        $intranet = $this->getIntranet();

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
            $user_id = intval($this->context->name());
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

            return new k_SeeOther($this->url('../', array('flare' => 'Permissions has been updated')));
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getValues()
    {
        return $this->context->getValues();
    }
}
