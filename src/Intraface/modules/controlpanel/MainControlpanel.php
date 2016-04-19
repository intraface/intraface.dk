<?php
/**
 *
 * @package Intraface_Administration
 * @author  Lars Olesen <lars@legestue.net>
 * @since   1.0
 * @version 1.0
 *
 */
class MainControlpanel extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'controlpanel';
        $this->menu_label = 'controlpanel';
        $this->all_has_access = true;
        $this->show_menu = 0;
        $this->active = 1;
        $this->menu_index = 340;
        $this->frontpage_index = 8;
        $this->shared = true;
        $this->required = true;

        $this->addPreloadFile('IntranetAdministration.php');
        $this->addPreloadFile('UserAdministration.php');
    }
}
