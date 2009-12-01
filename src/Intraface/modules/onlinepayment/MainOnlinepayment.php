<?php
/**
 * @package Intraface_OnlinePayment
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainOnlinepayment extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'onlinepayment';
        $this->menu_label = 'onlinepayment'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 70;
        $this->frontpage_index = 54;

        $this->addPreloadFile('OnlinePayment.php');

        $this->addSubmenuItem('quotations', '../debtor/quotation/list', 'module:quotation');
        $this->addSubmenuItem('orders', '../debtor/order/list', 'module:order');
        $this->addSubmenuItem('invoices', '../debtor/invoice/list', 'module:invoice');
        $this->addSubmenuItem('credit notes', '../debtor/credit_note/list', 'module:invoice');
        $this->addSubmenuItem('reminders', '../debtor/reminders', 'module:invoice');
        $this->addSubmenuItem('onlinepayments', '../onlinepayment/', 'module:onlinepayment');
        // $this->addSubmenuItem('settings', '../debtor/setting.php', 'module:debtor');

        $this->addControlpanelFile('OnlinePayment settings', 'module/onlinepayment/settings.php');

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