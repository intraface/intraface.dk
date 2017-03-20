<?php
/**
 * Kontakt
 *
 * @package Intraface_Contact
 * @author  Lars Olesen
 * @since   1.0
 * @version     1.0
 */
class MainContact extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'contact';
        $this->menu_label = 'Kontakter'; // Navnet der vil st� i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 120;
        $this->frontpage_index = 20;

        $this->addPreloadFile('Contact.php');
        $this->addPreloadFile('ContactMessage.php'); // kan m�ske slettes
        $this->addPreloadFile('ContactPerson.php');
         $this->addPreloadFile('ContactReminder.php');

        $this->addRequiredShared('email');
        $this->addRequiredShared('keyword');

        $this->addControlPanelFile('contact', 'core/restricted/module/contact/setting.php');

        $this->addFrontpageFile('include_frontpage.php');

        // Add sub menu items
        $this->addSubMenuItem("Reminders", "memos");

        // Add sub access items
        // opretkunde: et kort navn der er sigende
        // Opret ny kunde: En beskrivelse af subaccess.
        // $this->addSubAccessItem("opretkunde", "Opret ny kunde");

        // settings
        // paymentconditions in days
         $this->includeSettingFile('settings.php');

        $this->addSetting('paymentcondition', array(0, 8, 14, 30));
        $this->addSetting(
            'type',
            array(
                0 => 'private',
                1 => 'corporation'
            )
        );

         $this->addSetting('contact_login_url', array(
            0 => 'kundelogin.dk',
            1 => 'medlemslogin.dk'
         ));

         $this->addSetting(
             'preferred_invoice',
             array(
                1 => 'pdf',
                2 => 'email',
                3 => 'electronic'
             )
         );

         $this->addSetting(
             'reminder_status',
             array(
                0 => '_Invalid type_',
                1 => 'created',
                2 => 'seen',
                3 => 'cancelled'
             )
         );
    }
}
