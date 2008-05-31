<?php
/**
 * @package Intraface_Todo
 */
class MainProject extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'project';
        $this->menu_label = 'project'; // Navnet der vil stå i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 240;
        $this->frontpage_index = 110;
    }
}