<?php
class Intraface_modules_shop_Message_OrderConfirmation
{
    protected $shop;
    protected $order;

    function __construct($shop, $order)
    {
        $this->shop = $shop;
        $this->order = $order;
    }

    function getSubject()
    {

    }

    function getBody()
    {

    }
}
