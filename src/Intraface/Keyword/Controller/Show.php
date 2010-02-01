<?php
class Intraface_Keyword_Controller_Show extends k_Component
{
    function renderHtml()
    {
        return 'not implemented yet';
    }

    function renderHtmlDelete()
    {
        $this->getKernel()->useShared('keyword');
        $keyword = new Keyword($this->context->getModel(), $this->name());
        $keyword->delete();
        return new k_SeeOther($this->context->url('connect'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}