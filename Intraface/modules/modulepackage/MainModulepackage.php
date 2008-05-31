<?php
/**
 * @package Intraface_OnlinePayment
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainModulepackage extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'modulepackage';
        $this->menu_label = 'modulepackage'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 400;
        $this->frontpage_index = 9;

        $this->addPreloadFile('ModulePackage.php');
        $this->addFrontpageFile('include_front.php');
    }
}