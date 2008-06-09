<?php
class Demo_CMS_Root extends k_Controller
{
    function GET()
    {
        return get_class($this) . ' has intentionally been left blank';
    }

    function getPrivateKey()
    {
        return $this->context->getPrivateKey();
    }

    function forward($name)
    {
        $next = new Demo_CMS_Show($this, $name);
        return $next->handleRequest();
    }
}
