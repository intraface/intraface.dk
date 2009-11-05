<?php
class Intraface_modules_contact_Controller_Contactpersons extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_contact_Controller_Contactperson';
    }

    function renderHtml()
    {
        return new k_SeeOther($this->context->url());
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->context->getKernel()->module('contact');
        $translation = $this->context->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $contact = new Contact($this->context->getKernel(), $this->context->name());
    	$person = $contact->loadContactPerson(0);

        $smarty = new k_Template(dirname(__FILE__) . '/templates/contactperson-edit.tpl.php');
        return $smarty->render($this, array('person' => $person, 'contact' => $contact));
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $this->context->getKernel()->module('contact');
        $translation = $this->context->getKernel()->getTranslation('contact');

    	$contact = new Contact($this->context->getKernel(), $this->context->name());
    	$person = $contact->loadContactPerson(0);
    	if ($id = $person->save($_POST)) {
    		return new k_SeeOther($this->url('../'));
    	} else {
    		$value = $_POST;
    	}

    }
}