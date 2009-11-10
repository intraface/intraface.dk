<?php
/**
 * @package Intraface_Debtor
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 */
class MainDebtor extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'debtor'; // modulets slugnavn
        $this->menu_label = 'Debitor'; // Navnet der vil stå i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 60;
        $this->frontpage_index = 50;

        $this->addSubmenuItem('quotations', 'list.php?type=quotation', 'module:quotation');
        $this->addSubmenuItem('orders', 'list.php?type=order', 'module:order');
        $this->addSubmenuItem('invoices', 'list.php?type=invoice', 'module:invoice');
        $this->addSubmenuItem('credit notes', 'list.php?type=credit_note', 'module:invoice');
        $this->addSubmenuItem('reminders', 'reminders.php', 'module:invoice');
        $this->addSubmenuItem('onlinepayments', '../onlinepayment/', 'module:onlinepayment');
        $this->addSubmenuItem('currency', '../currency/', 'module:currency');
        //$this->addSubmenuItem('Indstillinger', 'setting.php');

        /*
        $this->addSetting('type', array(
            1=>'quotation',
            2=>'order',
            3=>'invoice',
            4=>'credit_note'
        ));
        */

        /*
        $this->addSetting('from', array(
            1=>'manuel',
            2=>'webshop',
            3=>'quotation',
            4=>'order',
            5=>'invoice'
        ));
        */


        // Denne status skal vi lige kigge på
        // Følgende status skal til:
        // Tilbud: Oprettet (created), Sendt (sent), Bestilt (executed), Afslået (canceled)
        // Ordre: Oprettet (created), Sendt (sent), Faktureret (executed), Annuleret (canceled)
        // Faktura: Oprettet (created), Sendt (sent), Betalt/Krediteret (executed), Afskrevet (canceled)
        // Kreditnota: Oprettet (created), Sendt (executed)
        //
        // Det vil sige at vi kan fjerne feltet sent i tabellen
        // Vi kan fjerne feltet locked, og i stedet angive at når den er executed, er den låst.
        // Vi kan fjerne feltet payed, men er nok nødt til på en anden måde at angive om den er krediteret. Enten en status mere eller et afskrevet felt.
        // Så skal der i stedet laves et felt mere der hedder bogført (booked)
        // Så skal der nok være felter status_1_date, status_2_date ... som angiver hvilken dato at det er kommet i hver status.

        $this->includeSettingFile('settings.php');

        /*
        // Følgende er et nyt forslag til status:
        $this->addSetting('status', array(
            0=>'created',
            1=>'sent',
            2=>'executed',
            3=>'cancelled'
        ));
        */

        /*
         * this can be removed after intraface 1.7 is up running.
        $this->addSetting('payment_method', array(
            0=>'Ingen',
            1=>'Kontooverførsel',
            2=>'Girokort +01',
            3=>'Girokort +71'
        ));
        */



        $this->addPreloadFile('Debtor.php');
        $this->addPreloadFile('DebtorItem.php');
        // $this->addPreloadFile('debtorFactory.php');
        // $this->addPreloadFile('DebtorSetting.php');

        $this->addControlpanelFile('debtor settings', 'core/restricted/module/debtor/settings');

        $this->addDependentModule('contact');
        $this->addDependentModule('product');

    }

    function getPath()
    {
        return url('/core/restricted/module/debtor/');
    }
}