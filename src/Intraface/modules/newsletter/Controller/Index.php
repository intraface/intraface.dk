<?php
class Intraface_modules_newsletter_Controller_Index extends k_Component
{
    protected $registry;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function map($name)
    {
        if ($name == 'lists') {
            return 'Intraface_modules_newsletter_Controller_Lists';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('lists'));
    }

    function getPage()
    {
        $registry = $this->registry->create();
    	return $registry->get('page');
    }

    function getHeader()
    {
        ob_start();
        $this->getPage()->start('Newsletter');
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function getFooter()
    {
        ob_start();
        $this->getPage()->end();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function wrapHtml($content)
    {
        return $this->getHeader() . $content . $this->getFooter();

    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    /*
    protected $registry;

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_List';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("newsletter");

        $smarty = new k_Template(dirname(__FILE__) . '/templates/lists.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        if (method_exists('getKernel', $this->context)) {
            return $this->context->getKernel();
        }
        $registry = $this->registry->create();
    	return $this->kernel = $registry->get('kernel');
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

    function getList()
    {
        $module = $this->getKernel()->module("newsletter");
        return $list = new NewsletterList($this->getKernel());
    }

    function renderHtmlCreate()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/list-edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $list = new NewsletterList($this->getKernel());
        if ($id = $list->save($_POST)) {
            return new k_SeeOther($this->url($id));
        } else {
            $value = $_POST;
        }
        return $this->render();
    }
    */
}