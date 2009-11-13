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
    	$contact = new Contact($this->context->getKernel(), $this->context->name());
    	$person = $contact->loadContactPerson(0);
    	if ($id = $person->save($_POST)) {
    		return new k_SeeOther($this->url('../'));
    	} else {
    		$value = $_POST;
    	}

    }
}