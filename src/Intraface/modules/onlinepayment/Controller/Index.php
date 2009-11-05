<?php
class Intraface_modules_onlinepayment_Controller_Index extends k_Component
{
    function renderHtml()
    {
        return new k_SeeOther($this->url('../../../../modules/onlinepayment/'));
    }
}