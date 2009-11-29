<?php
class Intraface_modules_shop_Controller_Root extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
         $this->template = $template;
    }

    function wrapHtml()
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/content.tpl.php');
        return $this->render($this, $data);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }
}