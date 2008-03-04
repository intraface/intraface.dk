<?php
/**
 *
 * @package <SystemMessage>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0
 *
 */

class SharedSystemmessage extends Shared
{
    function __construct()
    {
        $this->shared_name = "systemmessage"; // Navn på på mappen med modullet
        $this->active = 1; // Er shared aktivt

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile("IntranetNews.php");

        // Fil til med indstillinger man kan sætte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes på forsiden.
        // $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["shared_navn.setting"] = "Værdi";
        // $this->includeSettingFile("settings.php");
    }
}