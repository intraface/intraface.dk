<?php
class Demo_Newsletter_Root extends k_Component
{
    private $private_key;

    function map($name)
    {
        return 'Demo_Newsletter_Show';
    }

    function renderHtml()
    {
        return get_class($this) . ' intentionally left blank';
    }

    function getPrivateKey()
    {
        return $this->context->getPrivateKey();
    }
}