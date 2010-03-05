<?php
class Intraface_modules_onlinepayment_Language extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('onlinepayment_settings');
        // $this->hasColumn('intranet_id', 'integer', 11, array('greaterthan' => 0)); // defined in intranet template
        $this->hasColumn('email', 'string',  65555);
        $this->hasColumn('subject', 'string',  255);
    }

    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        $this->actAs('I18n', array('fields' => array('email', 'subject')));
    }

    function getConfirmationEmailSubject($language)
    {
    	return $this->Translation[$language]->subject;
    }

    function getConfirmationEmailBody($language)
    {
    	return $this->Translation[$language]->email;
    }
}
