<?php
class Intraface_modules_intranetmaintenance_Controller_User_Permission extends k_Component
{
    protected $user;
    public $method = 'put';
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/user/permission');
        return $smarty->render($this);
    }

    function postForm()
    {
        $module = $this->getKernel()->module("intranetmaintenance");

        $user = $this->getUser();
        $intranet = $this->getIntranet();

        $user->setIntranetId($this->query('intranet_id'));

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

            // Hvis en bruger retter sig selv, i det aktive intranet, s�tter vi adgang til dette modul
            if ($this->getKernel()->user->get("id") == $user->get("id") && $this->getKernel()->intranet->get("id") == $intranet->get("id")) {
                // Finder det aktive intranet
                $active_module = $this->getKernel()->getPrimaryModule();
                // Giver adgang til det
                $user->setModuleAccess($active_module->getId());
            }

            foreach ($modules as $module) {
                $user->setModuleAccess($module);
                if (!empty($sub_access[$module])) {
                    foreach ($sub_access[$module] as $subaccess) {
                        $user->setSubAccess($module, $subaccess);
                    }
                }
            }
            $user_id = $user->get('id');
            $edit_intranet_id = $this->query('intranet_id');

            return new k_SeeOther($this->url('../', array('intranet_id' => $this->query('intranet_id'), 'flare' => 'Permissions has been updated')));
        }

        return $this->render();
    }

    function getUser()
    {
        return ($this->context->getUser());
    }

    function getIntranet()
    {
        return $this->context->getIntranet();
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
