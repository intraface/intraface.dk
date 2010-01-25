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
        return $tpl->render($this, array('memos' => $gateway->getAll()));
    }

    function renderHtmlCreate()
    {
    	$reminder = new ContactReminder($this->context->getKernel());
        $contact = $reminder->contact;

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/memo-edit');
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