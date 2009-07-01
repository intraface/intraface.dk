<?php
class Demo_Newsletter_Root extends k_Controller
{
    private $private_key;

    function GET()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function getPrivateKey()
    {
        return $this->context->getPrivateKey();
    }

    function forward($name)
    {
        $next = new Demo_Newsletter_Show($this, $name);
        return $next->handleRequest();
    }
}