<?php
class Intraface_modules_contact_Controller_Memos extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_contact_Controller_Memo';
    }

    function renderHtml()
    {
        $gateway = new Intraface_modules_contact_MemosGateway($this->getKernel());

        $this->document->setTitle('Memos');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/memos');

        if (is_numeric($this->context->name())) {
            return $tpl->render($this, array('memos' => $gateway->findByContactId($this->context->name())));
        } else {
            return $tpl->render($this, array('memos' => $gateway->getAll()));
        }

    }

    function renderHtmlCreate()
    {
        $reminder = new ContactReminder($this->context->getKernel());
        $contact = $reminder->contact;

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/memo-edit');
        return $smarty->render($this, array('reminder' => $reminder, 'contact' => $contact));
    }

    function postForm()
    {
        $contact = $this->context->getContact();
        $reminder = new ContactReminder($contact);

        if ($id = $reminder->update($_POST)) {
            return new k_SeeOther($this->url('../'));
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
