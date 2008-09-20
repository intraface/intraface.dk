<?php
class Intraface_modules_currency_Controller_Root extends k_Dispatcher
{
    public $map = array(
        'currency' => 'Intraface_modules_currency_Controller_Index'
    );

    function getHeader()
    {
        $page = $this->registry->get('page');
        ob_start();
        $page->start($this->document->title);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function getFooter()
    {
        $page = $this->registry->get('page');
        ob_start();
        $page->end();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function execute()
    {
        return $this->forward('currency');
    }

    function handleRequest()
    {
        $content = parent::handleRequest();
        $data = array('content' => $content);
        return $this->getHeader() . $this->render(dirname(__FILE__) . '/tpl/content.tpl.php', $data) . $this->getFooter();
    }
}