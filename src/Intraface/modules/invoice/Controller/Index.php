<?php
class Intraface_modules_invoice_Controller_Index extends k_Component
{
    function renderHtml()
    {
        return new k_SeeOther($this->url('../debtor/invoice/list'));
    }
}
