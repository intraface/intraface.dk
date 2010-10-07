<?php
class Demo_CMS_Root extends k_Component
{
    function renderHtml()
    {
        return get_class($this) . ' has intentionally been left blank';
    }

    function getPrivateKey()
    {
        return $this->context->getPrivateKey();
    }

    function map($name)
    {
        return 'Demo_CMS_Show';
    }
}
