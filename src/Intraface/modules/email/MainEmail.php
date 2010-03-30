<?php
/**
 * @package Intraface_Email
 */
class MainEmail extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'email';
        $this->menu_label = 'email'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 210;
        $this->frontpage_index = 200;
        $this->shared = true;

        $this->addRequiredShared('email');
        
        $this->addControlpanelFile('E-mail settings', 'core/restricted/module/email/settings');
        
    }
}
