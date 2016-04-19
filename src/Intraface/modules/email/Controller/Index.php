<?php
class Intraface_modules_email_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'settings') {
            return 'Intraface_modules_email_Controller_Settings';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_email_Controller_Email';
        }
    }

    function dispatch()
    {
        if ($this->query('contact_id')) {
            $this->url_state->set('contact_id', $this->query('contact_id'));
        }

        return parent::dispatch();
    }

    function renderHtml()
    {
        $this->getKernel()->module('email');
        $contact_module = $this->getKernel()->useModule('contact');
        $email_shared = $this->getKernel()->useShared('email');

        $emails = $this->getGateway();

        if (!$this->query()) {
        }
        $emails->getDBQuery()->usePaging('paging');
        $emails->getDBQuery()->storeResult('use_stored', 'emails', 'toplevel');
        $emails->getDBQuery()->setUri($this->url());

        if ($this->query("contact_id")) {
            $emails->getDBQuery()->setCondition("email.contact_id = ".intval($this->query("contact_id")));
        }

        if ($this->query('filter') == 'new') {
            $emails->getDBQuery()->setSorting("email.date_created DESC");
        } else {
            $emails->getDBQuery()->useCharacter();
            $emails->getDBQuery()->defineCharacter('character', 'email.subject');
            $emails->getDBQuery()->setSorting("email.date_sent DESC");
        }

        $queue = $emails->countQueue();

        $data = array(
            'queue' => $queue,
            'emails' => $emails->getAll(),
            'gateway' => $emails,
            'contact_module' => $contact_module,
            'email_shared' => $email_shared
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getGateway()
    {
        return new Intraface_shared_email_EmailGateway($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
