<?php
class Intraface_modules_shop_Shop extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('shop');
        $this->hasColumn('name',                         'string',  255);
        $this->hasColumn('description',                  'string',  65555);
        $this->hasColumn('identifier',                   'string',  255);
        $this->hasColumn('show_online',                  'integer', 1);
        $this->hasColumn('send_confirmation',            'integer', 1);
        $this->hasColumn('confirmation_subject',         'string',  255);
        $this->hasColumn('confirmation_greeting',        'string',  255);
        $this->hasColumn('confirmation',                 'string',  65555);
        $this->hasColumn('confirmation_add_contact_url', 'integer', 1);
        $this->hasColumn('receipt',                      'string',  65555);
        $this->hasColumn('payment_link',                 'string',  255);
        $this->hasColumn('payment_link_add',             'integer', 1);
        $this->hasColumn('terms_of_trade_url',           'string',  255);
    }
    
    public function setUp()
    {
        $this->actAs('Intraface_Doctrine_Template_Intranet');
        //$this->loadTemplate('Intraface_Doctrine_Template_Intranet');
    }

    function getId()
    {
        return $this->id;
    }
    
    public function getName() 
    {
        return $this->name;
    }

    function getConfirmationSubject()
    {
        return $this->confirmation_subject;
    }
    
    function getConfirmationText()
    {
        return $this->confirmation;
    }
    
    function getConfirmationGreeting()
    {
        return $this->confirmation_greeting;
    }    
    
    function showLoginUrl()
    {
        return $this->confirmation_add_contact_url;
    }

    function getPaymentUrl()
    {
        return $this->payment_link;
    }

    function showPaymentUrl()
    {
        return $this->payment_link_add;
    }
    
    function sendConfirmation()
    {
        return $this->send_confirmation;
    } 
    
    function getTermsOfTradeUrl()
    {
        return $this->terms_of_trade_url;
    }   
}