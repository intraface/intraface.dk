<?php
/**
 * @package Intraface_Email
 */
class MainFileimport extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'fileimport';
        $this->menu_label = 'fileimport'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 210;
        $this->frontpage_index = 200;
        $this->shared = true;
        $this->required = true;

        $this->addRequiredShared('fileimport');
    }
}
