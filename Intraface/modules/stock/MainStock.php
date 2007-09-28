<?php
/**
 *
 * @package Intraface_Stock
 * @author	Sune Jensen
 * @since	1.0
 * @version	1.0
 *
 */
class MainStock Extends Main {

    function MainStock() {
        $this->module_name = 'stock'; // Navn på på mappen med modullet
        $this->menu_label = 'stock'; // Navn er det skal stå i menuen
        $this->show_menu = 0; // Skal modullet være vist i menuen
        $this->active = 1; // Er modullet aktivt
        $this->menu_index = 155;
        $this->frontpage_index = 35;

        // Tilføjer et undermenupunkt
        // $this->addSubMenuItem("Underside", "underside.php");
        // Tilføjer undermenupunkt, der kun vises når hvis man har sub_acces'en vat_report
        // $this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate");
        // Tilføjer undermenupunkt, der kun vises når hvis man har adgang til modullet backup
        // $this->addSubMenuItem("Årsafslutning", "end.php", "module:backup");

        // Tilføjer en subaccess
        // $this->addSubAccessItem("canCreate", "Rettighed til at oprette");

        // Tilføjer en setting, som er ens for alle intranet. Se længere nede
        // $this->addSetting("payment_method", array("Dankort", "Kontant");

        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile('Stock.php');

        // Fil til med indstillinger man kan sætte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes på forsiden.
        // $this->addFrontpageFile('include_front.php');

        // Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["modul_navn.setting"] = "Værdi";
        // $this->includeSettingFile("settings.php");

        // Dependent module vil automatisk blive inkluderet på siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
        // $this->addDependentModule("pdf");
    }
}


/*
SETTING:
Setting kan bruges til at sætte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hjælp af $module_object->getSetting("payment_method")



*/
?>