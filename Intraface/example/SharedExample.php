<?php
/**
 *
 * @package <shared_navn>
 * @author	<name>
 * @version	1.0
 */
class SharedExample Extends Shard {

    function SharedExample() {
        $this->shared_name = "example"; // Navn p� p� mappen med modullet
        $this->active = 1; // Er shared aktivt

        // Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
        $this->addSetting("payment_method", array("Dankort", "Kontant"));

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile("fil.php");

        // Fil til med indstillinger man kan s�tte i modullet
        $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes p� forsiden.
        $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["shared_navn.setting"] = "V�rdi";
        $this->includeSettingFile("settings.php");


    }
}


/*
SETTING:
Setting kan bruges til at s�tte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hj�lp af $module_object->getSetting("payment_method")



*/
?>