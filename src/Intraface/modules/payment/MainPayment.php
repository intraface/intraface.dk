<?php
/**
 * @package Intraface_Payment
 * @author  Lars Olesen <lars@legestue.net>
 * @since   1.0
 * @version 1.0
 */
class MainPayment extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'payment'; // modulets slugnavn
        $this->menu_label = 'Payment'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 60;
        $this->frontpage_index = 50;
    }
}
