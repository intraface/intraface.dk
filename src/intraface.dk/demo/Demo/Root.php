<?php
class Demo_Root extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Demo_Identifier';
    }

    function renderHtml()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create('main');
        //$this->document->company_name = 'Intraface Demo';
        $this->document()->addStyle($this->url('/layout.css'));
        $this->document()->addStyle($this->url('/shop.css'));
        return $tpl->render($this, array('content' => $content));
    }
}
