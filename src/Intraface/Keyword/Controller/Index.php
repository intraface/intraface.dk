<?php
class Intraface_Keyword_Controller_Index extends k_Component
{
    public $map = array('connect' => 'Intraface_Keyword_Controller_Connect');

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_Keyword_Controller_Show';
        }
        return $this->map[$name];
    }

    function renderHtml()
    {
        return get_class($this) . ': intentionally left blank';
    }

    function getModel()
    {
    	return $this->context->getModel();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}