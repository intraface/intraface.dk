<?php
/**
 * Language
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
class MainLanguage extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'language'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 40;
        $this->frontpage_index = 10;
    }
}
