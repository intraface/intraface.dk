<?php
/**
 *
 * @package Intraface_Administration
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainAdministration extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'administration';
        $this->menu_label = 'administration';
        $this->show_menu = 0;
        $this->active = 1;
        $this->menu_index = 340;
        $this->frontpage_index = 8;

        $this->addPreloadFile('IntranetAdministration.php');
        $this->addPreloadFile('UserAdministration.php');

        $this->addFrontpageFile('include_frontpage.php');

        /*
        $this->addSubMenuItem("Intranet", "intranets.php");
        $this->addSubMenuItem("Brugere", "users.php");
        $this->addSubMenuItem("Registrér moduler", "register_modules.php");
        */
    }
}
