<?php
class Intraface_modules_contact_Controller_Contactpersons extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

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
        $contact = $this->context->getContact();
        $person = $contact->loadContactPerson(0);

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/contactperson-edit');
        return $smarty->render($this, array('person' => $person, 'contact' => $contact));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $contact = $this->context->getContact();
        $person = $contact->loadContactPerson(0);
        if ($id = $person->save($_POST)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $value = $_POST;
        }
        return $this->render();
    }
}
