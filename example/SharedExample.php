<?php
/**
 *
 * @package <shared_navn>
 * @author	<name>
 * @since	1.0
 * @version	1.0
 *
 */
class SharedExample Extends Shard {

	function SharedExample() {
		$this->shared_name = "example"; // Navn på på mappen med modullet
		$this->active = 1; // Er shared aktivt

		// Tilføjer en setting, som er ens for alle intranet. Se længere nede
		$this->addSetting("payment_method", array("Dankort", "Kontant"));

		// Filer der skal inkluderes ved opstart af modul.
		$this->addPreloadFile("fil.php");

		// Fil til med indstillinger man kan sætte i modullet
		$this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

		// Fil der inkluderes på forsiden.
		$this->addFrontpageFile('include_front.php');

		// Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["shared_navn.setting"] = "Værdi";
		$this->includeSettingFile("settings.php");


	}
}


/*
SETTING:
Setting kan bruges til at sætte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hjælp af $module_object->getSetting("payment_method")



*/
?>