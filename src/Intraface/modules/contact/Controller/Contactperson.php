<?php
class Intraface_modules_contact_Controller_Contactperson extends k_Component
{
    function renderHtml()
    {
        return 'Intentionally left blank';
    }

    function renderHtmlEdit()
    {
    	$contact = new Contact($this->context->getKernel(), $this->context->context->name());
    	$person = $contact->loadContactPerson($this->name());

        $smarty = new k_Template(dirname(__FILE__) . '/templates/contactperson-edit.tpl.php');
        return $smarty->render($this, array('contact' => $contact, 'person' => $person));

    }

    function postForm()
    {
    	$contact = new Contact($this->context->getKernel(), $this->context->context->name());
    	$person = $contact->loadContactPerson($this->name());
    	if ($id = $person->save($_POST)) {
    		return new k_SeeOther($this->url('../'));
    	} else {
    		$value = $_POST;
    	}
    }
}