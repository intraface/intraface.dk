<?php
/**
 * @package Intraface_Procurement
 * @author	<name>
 * @since	1.0
 * @version	1.0
 *
 */
class MainProcurement Extends Main {

    function mainProcurement() {
        $this->module_name = 'procurement'; // Navn på på mappen med modullet
        $this->menu_label = 'procurement'; // Navn er det skal stå i menuen
        $this->show_menu = 1; // Skal modullet være vist i menuen
        $this->active = 1; // Er modullet aktivt
        $this->menu_index = 90;
        $this->frontpage_index = 60;

        // Tilføjer et undermenupunkt
        // $this->addSubMenuItem("Underside", "underside.php");
        // Tilføjer undermenupunkt, der kun vises når hvis man har sub_acces'en vat_report
        // $this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate");
        // Tilføjer undermenupunkt, der kun vises når hvis man har adgang til modullet backup
        // $this->addSubMenuItem("Årsafslutning", "end.php", "module:backup");

        // Tilføjer en subaccess
        // $this->addSubAccessItem("canCreate", "Rettighed til at oprette");

        // Tilføjer en setting, som er ens for alle intranet. Se længere nede
        // Status med følgende flow: bestilt (ordered), modtaget (recieved), annulleret (canceled)
        $this->addSetting('status', array(
                                            0=>'ordered',
                                            1=>'recieved',
                                            2=>'canceled')
        );

        $this->addSetting('from_region', array(
                                            0=>'denmark',
                                            1=>'eu',
                                            2=>'eu_vat_registered',
                                            3=>'outside_eu')
        );



        // Filer der skal inkluderes ved opstart af modul.
        $this->addPreloadFile('Procurement.php');
        $this->addPreloadFile('ProcurementItem.php');

        $this->addDependentModule('product');

        // Fil til med indstillinger man kan sætte i modullet
        // $this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

        // Fil der inkluderes på forsiden.
        $this->addFrontpageFile('include_frontpage.php');

        // Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["modul_navn.setting"] = "Værdi";
        // $this->includeSettingFile("settings.php");

        // Dependent module vil automatisk blive inkluderet på siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
        // $this->addDependentModule("pdf");





    }
}
?>