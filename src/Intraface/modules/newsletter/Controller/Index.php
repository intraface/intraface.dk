<?php
class Intraface_modules_newsletter_Controller_Index extends k_Component
{
    protected $registry;
    protected $page;

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
}