<?php
/**
 *
 * @package <modul_navn>
 * @author	<name>
 * @version	1.0
 */
class MainExample Extends Main {

    function MainExample() {
        $this->module_name = "example"; // Navn p� p� mappen med modullet
        $this->menu_label = "Eksempel"; // Navn er det skal st� i menuen
        $this->show_menu = 1; // Skal modullet v�re vist i menuen
        $this->active = 1; // Er modullet aktivt
        $this->menu_index = 100; // The position on the menuen
        $this->frontpage_index = 10; // The position on the frontpage.

        // Tilf�jer et undermenupunkt
        $this->addSubMenuItem("Underside", "underside.php");
        // Tilf�jer undermenupunkt, der kun vises n�r hvis man har sub_acces'en vat_report
        $this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate");
        // Tilf�jer undermenupunkt, der kun vises n�r hvis man har adgang til modullet backup
        $this->addSubMenuItem("�rsafslutning", "end.php", "module:backup");

        // Tilf�jer en subaccess
        $this->addSubAccessItem("canCreate", "Rettighed til at oprette");

        // Tilf�jer en setting, som er ens for alle intranet. Se l�ngere nede
        $this->addSetting("payment_method", array("Dankort", "Kontant"));

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile("fil.php");

        // Fil til med indstillinger man kan s�tte i modullet
        $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes p� forsiden.
        $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bem�rk ikke den sammme indstilling som addSetting(). Filen skal indeholde f�lgende array: $_setting["modul_navn.setting"] = "V�rdi";
        $this->includeSettingFile("settings.php");

        // Dependent module vil automatisk blive inkluderet p� siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
        $this->addDependentModule("pdf");

        // Inkludere et shared i modullet.
        $this->addRequiredShared("filehandler");
    }
}


/*
SETTING:
Setting kan bruges til at s�tte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hj�lp af $module_object->getSetting("payment_method")



*/
?>