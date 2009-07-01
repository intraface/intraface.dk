<?php
class Demo_Root extends k_Dispatcher
{
    function __construct()
    {
        parent::__construct();
        $this->document->template = dirname(__FILE__) . '/../main-tpl.php';
        $this->document->company_name = 'Intraface Demo';
        $this->document->styles[] = $this->url('/layout.css');
        $this->document->styles[] = $this->url('/shop.css');
    }

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function forward($name)
    {
        $next = new Demo_Identifier($this, $name);
        return $next->handleRequest();
    }
}