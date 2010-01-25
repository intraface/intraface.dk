<?php
class Intraface_modules_newsletter_Controller_Lists extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_List';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/lists');
        return $smarty->render($this);
    }

    function renderHtmlCreate()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/list-edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $list = new NewsletterList($this->getKernel());
        if ($id = $list->save($_POST)) {
            return new k_SeeOther($this->url($id));
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }

    function getList()
    {
        $module = $this->getKernel()->module("newsletter");
        return $list = new NewsletterList($this->getKernel());
    }

    function getValues()
    {
        return array();
    }
}