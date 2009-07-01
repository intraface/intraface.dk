<?php
/**
 * @package Intraface_OnlinePayment
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainCurrency extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'currency';
        $this->menu_label = 'currency'; // Navnet der vil stå i menuen
        $this->show_menu = 0; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 80;
        $this->frontpage_index = 55;

        
        $this->addSubmenuItem('quotations', '../debtor/list.php?type=quotation', 'module:quotation');
        $this->addSubmenuItem('orders', '../debtor/list.php?type=order', 'module:order');
        $this->addSubmenuItem('invoices', '../debtor/list.php?type=invoice', 'module:invoice');
        $this->addSubmenuItem('credit notes', '../debtor/list.php?type=credit_note', 'module:invoice');
        $this->addSubmenuItem('reminders', '../debtor/reminders.php', 'module:invoice');
        $this->addSubmenuItem('onlinepayments', '../onlinepayment/', 'module:onlinepayment');

        // $this->addControlpanelFile('OnlinePayment settings', 'modules/onlinepayment/settings.php');
        // $this->includeSettingFile('settings.php');
        // $this->addFrontpageFile('include_front.php');
    }
}