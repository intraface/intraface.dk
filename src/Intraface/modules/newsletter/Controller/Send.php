<?php
class Intraface_modules_newsletter_Controller_Send extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/send');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getList()
    {
        return $this->context->getList();
    }

    function getLetter()
    {
        return $this->context->getLetter();
    }

    function postForm()
    {
        if ($this->context->getLetter()->queue()) {
            return new k_SeeOther($this->url('../', array('flare' => 'Newsletter has been sent')));
        }
        return $this->render();
    }
}
