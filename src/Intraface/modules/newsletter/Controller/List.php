<?php
class Intraface_modules_newsletter_Controller_List extends k_Component
{
    protected $registry;

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

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $this->getKernel()->module('newsletter');

        $list = new NewsletterList($this->getKernel(), $this->name());
        $value = $list->get();
        $letter = new Newsletter($list);
        $letters = $letter->getList();

        $smarty = new k_Template(dirname(__FILE__) . '/templates/list.tpl.php');
        return $smarty->render($this);
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

    function t($phrase)
    {
         return $phrase;
    }

    function DELETE()
    {
    	$list = new NewsletterList($this->getKernel(), $this->name());
    	$list->delete();
    }

    function renderHtmlEdit()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/list-edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $list = new NewsletterList($this->getKernel(), $this->name());
        if ($id = $list->save($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $value = $_POST;
        }
        return $this->render();
    }

}
