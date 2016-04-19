<?php
/**
 *
 * @package Intraface
 * @author  <Sune>
 * @since   1.0
 * @version     1.0
 *
 */
class SharedFilehandler extends Intraface_Shared
{
    function __construct()
    {
        $this->shared_name = 'filehandler'; // Navn på på mappen med modullet
        $this->active = 1; // Er shared aktivt

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile('FileHandler.php');
        $this->addPreloadFile('FileHandlerHTML.php');

        // Fil til med indstillinger man kan sætte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes på forsiden.
        // $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["shared_navn.setting"] = "Værdi";
        $this->includeSettingFile('settings.php');
    }
}
