<?php
/**
 * @package Intraface_Invoice
 * @author	Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 *
 */
class MainInvoice extends Main
{
    function __construct()
    {
        $this->module_name     = 'invoice'; // Navnet der vil stå i menuen
        $this->menu_label      = 'invoice'; // Navnet der vil stå i menuen
        $this->show_menu       = 0; // Skal modulet vises i menuen.
        $this->active          = 1; // Er modulet aktivt.
        $this->menu_index      = 66;
        $this->frontpage_index = 53;

        $this->addPreloadFile('Invoice.php');
        $this->addPreloadFile('Reminder.php');
        $this->addPreloadFile('ReminderItem.php');
        $this->addPreloadFile('CreditNote.php');

        $this->addSetting('payment_for', array(
            0=>'manuel',
            1=>'invoice',
            2=>'reminder')
        );
        $this->addSetting('payment_type', array(
            -1=>'depriciation',
            0=>'bank_transfer',
            1=>'giro_transfer',
            2=>'credit_card',
            3=>'cash')
        );

        $this->addPreloadFile('Payment.php');

        $this->addFrontpageFile('include_front.php');

        $this->includeSettingFile('settings.php');

    }
}

?>