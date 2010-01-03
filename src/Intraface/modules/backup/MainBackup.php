<?php
/**
 * Backup
 *
 * @package Intraface_Backup
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
class MainBackup extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'backup';
        $this->menu_label = 'backup'; // menu name
        $this->show_menu = 1; // show in menu?
        $this->active = 1; // is module active
        $this->menu_index = 520;
        $this->frontpage_index = 200;
    }
}
