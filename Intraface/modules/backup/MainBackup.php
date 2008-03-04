<?php
/**
 * Til backup
 *
 * @package Intraface_Backup
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
class MainBackup extends Main {

    function __construct()
    {
        $this->module_name = 'backup';
        $this->menu_label = 'backup'; // Navnet der vil stå i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 520;
        $this->frontpage_index = 200;

        // Tilføj undermenu punkter.
        // $this->addSubMenuItem("Underside 1", "underside1.php");
        // $this->addSubMenuItem("Underside 2", "underside2.php");

        // Tilføj subaccess punkter
        // opretkunde: et kort navn der er sigende
        // Opret ny kunde: En beskrivelse af subaccess.
        //$this->addSubAccessItem("opretkunde", "Opret ny kunde");

    }
}
