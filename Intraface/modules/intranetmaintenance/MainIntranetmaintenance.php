<?php
/**
 *
 * @package Intraface_IntranetMaintenance
 * @author	Sune Jensen
 * @since	1.0
 * @version	1.1
 *
 */
class MainIntranetMaintenance extends Main
{
    function __construct()
    {
        $this->module_name = 'intranetmaintenance';
        $this->menu_label = 'intranetmaintenance';
        $this->show_menu = 1;
        $this->active = 1;
        $this->menu_index = 500;
        $this->frontpage_index = 200;

        $this->addSubMenuItem('intranet', 'index.php');
        $this->addSubMenuItem('modules', 'modules.php');
        $this->addSubMenuItem('messages', 'messages.php');
        $this->addSubMenuItem('delete intranet', 'delete_intranet.php');


        $this->addPreloadFile('IntranetMaintenance.php');
        $this->addPreloadFile('ModuleMaintenance.php');
        // $this->addPreloadFile('SubAccessMaintenance.php');
        $this->addPreloadFile('UserMaintenance.php');
    }
}