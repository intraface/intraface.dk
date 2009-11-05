<?php
class Intraface_modules_order_Controller_Index extends k_Component
{
    function renderHtml()
    {
        return new k_SeeOther($this->url('../debtor/order/list'));
    }
}