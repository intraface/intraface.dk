<?php
/**
 * @package Intraface_Email
 */


class MainEmail Extends Main {

    function MainEmail() {
        $this->module_name = 'email';
        $this->menu_label = 'email'; // Navnet der vil stå i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 210;
        $this->frontpage_index = 200;

        $this->addRequiredShared('email');
    }

}

?>