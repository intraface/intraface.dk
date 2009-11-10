<?php
class Intraface_Keyword_Controller_Index extends k_Component
{
    public $map = array('connect' => 'Intraface_Keyword_Controller_Connect',
                        'edit'    => 'Intraface_Keyword_Controller_Edit');

    function map($name)
    {
        return $this->map[$name];
    }

    function renderHtml()
    {
        return get_class($this) . ': intentionally left blank';
    }

    function getObject()
    {
    	return $this->context->getObject();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}