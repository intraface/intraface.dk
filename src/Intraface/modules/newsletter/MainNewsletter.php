<?php
/**
 * @package Intraface_Newsletter
 * @author  Lars Olesen
 * @since   1.0
 * @version     1.0
 */
class MainNewsletter extends Intraface_Main
{
    function __construct()
    {
        $this->module_name = 'newsletter';
        $this->menu_label = 'newsletter';
        $this->show_menu = 1;
        $this->active = 1;
        $this->menu_index = 200;
        $this->frontpage_index = 100;

        $this->addPreloadFile('Newsletter.php');
        $this->addPreloadFile('NewsletterList.php');
        $this->addPreloadFile('NewsletterSender.php');
        $this->addPreloadFile('NewsletterSubscriber.php');
        // $this->addFrontpageFile('include_front.php');

        $this->addDependentModule('contact');
        $this->addRequiredShared('email');

        $this->addSubAccessItem('sendnewsletter', 'send newsletter');
        $this->includeSettingFile('settings.php');
    }
}
