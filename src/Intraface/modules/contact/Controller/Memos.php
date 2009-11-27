<?php
class Intraface_modules_contact_Controller_Memos extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_contact_Controller_Memo';
    }

    function renderHtml()
    {
        return new k_SeeOther($this->context->url());
    }

    function renderHtmlCreate()
    {
    	$reminder = new ContactReminder($this->context->getKernel());
        $contact = $reminder->contact;

        $smarty = new k_Template(dirname(__FILE__) . '/templates/memo-edit.tpl.php');
        return $smarty->render($this, array('reminder' => $reminder, 'contact' => $contact));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $contact = new Contact($this->context->getKernel(), (int)$this->context->name());
   		$reminder = new ContactReminder($contact);

    	if ($id = $reminder->update($_POST)) {
    		return new k_SeeOther($this->url('../'));
    	}
    }
}