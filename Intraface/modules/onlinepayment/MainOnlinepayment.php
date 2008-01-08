<?php
/**
 * @package Intraface_OnlinePayment
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainOnlinepayment Extends Main {
    function MainOnlinepayment() {
        $this->module_name = 'onlinepayment';
        $this->menu_label = 'onlinepayment'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 70;
        $this->frontpage_index = 54;

        $this->addPreloadFile('OnlinePayment.php');

        $this->addSubmenuItem('quotations', '../debtor/list.php?type=quotation', 'module:quotation');
        $this->addSubmenuItem('orders', '../debtor/list.php?type=order', 'module:order');
        $this->addSubmenuItem('invoices', '../debtor/list.php?type=invoice', 'module:invoice');
        $this->addSubmenuItem('credit notes', '../debtor/list.php?type=credit_note', 'module:invoice');
        $this->addSubmenuItem('reminders', '../debtor/reminders.php', 'module:invoice');
        $this->addSubmenuItem('onlinepayments', '../onlinepayment/', 'module:onlinepayment');
        $this->addSubmenuItem('settings', '../debtor/setting.php');

        $this->addControlpanelFile('OnlinePayment settings', 'modules/onlinepayment/settings.php');


         $this->includeSettingFile('settings.php');

        /*
        $this->addSetting('status', array(
            0 => '',
            1 => 'created',
            2 => 'authorized',
            3 => 'captured',
            4 => 'reversed',
            5 => 'cancelled'));
        */

        /*
        $this->addSetting('belong_to', array(
            0 => '',
            1 => 'order',
            2 => 'invoice'));
        */
        
        $this->addSetting('implemented_providers',  array(
            0 => '_invalid_',
            1 => 'default', // reserveret for en custom (altså en hvor det hele kører uden for systemet
            2 => 'quickpay',
            3 => 'dandomain'
        ));

        $this->addFrontpageFile('include_front.php');
    }
}
?>