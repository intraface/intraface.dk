<?php
class Intraface_modules_modulepackage_Controller_Index extends k_Component
{
    function renderHtml()
    {
        return new k_SeeOther($this->url('../../../../modules/modulepackage'));
    }
}