<?php
/**
 *
 * @package <modul_navn>
 * @author	<name>
 * @since	1.0
 * @version	1.0
 *
 */
class MainExample Extends Main {

	function MainExample() {
		$this->module_name = "example"; // Navn på på mappen med modullet
		$this->menu_label = "Eksempel"; // Navn er det skal stå i menuen
		$this->show_menu = 1; // Skal modullet være vist i menuen
		$this->active = 1; // Er modullet aktivt
		$this->menu_index = 100; // The position on the menuen
		$this->frontpage_index = 10; // The position on the frontpage.

		// Tilføjer et undermenupunkt
		$this->addSubMenuItem("Underside", "underside.php");
		// Tilføjer undermenupunkt, der kun vises når hvis man har sub_acces'en vat_report
		$this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate");
		// Tilføjer undermenupunkt, der kun vises når hvis man har adgang til modullet backup
		$this->addSubMenuItem("Årsafslutning", "end.php", "module:backup");

		// Tilføjer en subaccess
		$this->addSubAccessItem("canCreate", "Rettighed til at oprette");

		// Tilføjer en setting, som er ens for alle intranet. Se længere nede
		$this->addSetting("payment_method", array("Dankort", "Kontant"));

		// Filer der skal inkluderes ved opstart af modul.
		$this->addPreloadFile("fil.php");

		// Fil til med indstillinger man kan sætte i modullet
		$this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

		// Fil der inkluderes på forsiden.
		$this->addFrontpageFile('include_front.php');

		// Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["modul_navn.setting"] = "Værdi";
		$this->includeSettingFile("settings.php");

		// Dependent module vil automatisk blive inkluderet på siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
		$this->addDependentModule("pdf");

		// Inkludere et shared i modullet.
		$this->addRequiredShared("filehandler");
	}
}


/*
SETTING:
Setting kan bruges til at sætte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hjælp af $module_object->getSetting("payment_method")



*/
?>