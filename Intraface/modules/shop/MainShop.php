<?php
/**
 * @package Intraface_Shop
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainShop extends Main
{
    function __construct()
    {
        $this->module_name     = 'shop';
        $this->menu_label      = 'Shop'; // Navnet der vil stå i menuen
        $this->show_menu       = 1; // Skal modulet vises i menuen.
        $this->active          = 1; // Er modulet aktivt.
        $this->menu_index      = 170;
        $this->frontpage_index = 90;

        $this->addSetting('show_online', array(
            0 => 'only_stock',
            1 => 'all_published'
        ));

    }
}