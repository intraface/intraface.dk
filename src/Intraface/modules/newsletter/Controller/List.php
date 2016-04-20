<?php
class Intraface_modules_newsletter_Controller_List extends k_Component
{
    protected $template;

    protected function map($name)
    {
        if ($name == 'subscribers') {
            return 'Intraface_modules_newsletter_Controller_Subscribers';
        } elseif ($name == 'letters') {
            return 'Intraface_modules_newsletter_Controller_Letters';
        } elseif ($name == 'log') {
            return 'Intraface_modules_newsletter_Controller_Log';
        }
    }

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->getKernel()->module('newsletter');

        $list = new NewsletterList($this->getKernel(), $this->name());
        $value = $list->get();
        $letter = new Newsletter($list);
        $letters = $letter->getList();

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/list');
        return $smarty->render($this);
    }
    
    function renderHtmlDelete()
    {
        if ($this->DELETE()) {
            return new k_SeeOther($this->url('../', array('flare' => 'List has been deleted')));
        }
        return $this->render();
    }
    

    function DELETE()
    {
        return $this->getList()->delete();
    }

    function renderHtmlEdit()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/list-edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $list = $this->getList();
        if ($id = $list->save($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $value = $_POST;
        }
        return $this->render();
    }

    function getList()
    {
        $this->getKernel()->module('newsletter');
        return new NewsletterList($this->getKernel(), $this->name());
    }

    function getValues()
    {
        return $this->getList()->get();
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
}
