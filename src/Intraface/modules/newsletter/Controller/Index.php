<?php
class Intraface_modules_newsletter_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'lists') {
            return 'Intraface_modules_newsletter_Controller_Lists';
        }
    }

    function renderHtml()
    {
        if ($this->query('contact_id')) {
            $gateway = new Intraface_modules_newsletter_ListGateway($this->getKernel());
            $lists = $gateway->findByContactId($this->query('contact_id'));
            $tpl = $this->template->create(dirname(__FILE__) . '/templates/contact-lists');
            return $tpl->render($this, array('lists' => $lists));
        }

        return new k_SeeOther($this->url('lists'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
