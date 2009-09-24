<?php
class Intraface_modules_intranetmaintenance_Controller_Intranet_Permission extends k_Component
{
    protected $registry;
    protected $intranetmaintenance;

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
        return $this->context->getIntranet();
    }

    function putForm()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $intranet = $this->getIntranet();

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

            return new k_SeeOther($this->url('../', array('flare' => 'Permissions has been updated')));
        }

        throw new Exception('Did not validate');
    }
}


