<?php
class Intraface_modules_contact_Controller_Contactperson extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        return 'Intentionally left blank';
    }

    function renderHtmlEdit()
    {
        $contact = $this->context->context->getContact();
        $person = $contact->loadContactPerson($this->name());

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/contactperson-edit');
        return $smarty->render($this, array('contact' => $contact, 'person' => $person));
    }

    function postForm()
    {
        $contact = $this->context->context->getContact();
        $person = $contact->loadContactPerson($this->name());
        if ($id = $person->save($_POST)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $value = $_POST;
        }
        return $this->render();
    }
}
