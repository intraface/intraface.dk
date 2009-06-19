<?php
require_once dirname(__FILE__) . '/../config.test.php';

class LanguageTest extends PHPUnit_Framework_TestCase
{
    function testLanguageSave()
    {
        $intranet_id = 1;
        Intraface_Doctrine_Intranet::singleton($intranet_id);
        $settings = new Intraface_modules_onlinepayment_Language;
        $settings->Translation['da']->email = 'danish email';
        $settings->Translation['da']->subject = 'danish subject';
        $settings->Translation['en']->subject = 'english subject';
        $settings->Translation['en']->email = 'english email';
        $settings->save();

        $settings = Doctrine::getTable('Intraface_modules_onlinepayment_Language')->findOneByIntranetId($intranet_id);
        $settings->Translation['da']->email = 'danish email 2';
        $settings->Translation['da']->subject = 'danish subject 2';
        $settings->Translation['en']->subject = 'english subject 2';
        $settings->Translation['en']->email = 'english email 2';
        $settings->save();

        $language = 'da';

        $this->assertEquals('danish email 2', $settings->getConfirmationEmailBody($language));
        $this->assertEquals('danish subject 2', $settings->getConfirmationEmailSubject($language));

        $language = 'en';

        $this->assertEquals('english email 2', $settings->getConfirmationEmailBody($language));
        $this->assertEquals('english subject 2', $settings->getConfirmationEmailSubject($language));

    }

}
