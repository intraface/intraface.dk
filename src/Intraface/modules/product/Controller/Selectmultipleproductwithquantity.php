<?php
class Intraface_modules_product_Controller_Selectmultipleproductwithquantity extends Intraface_modules_product_Controller_Selectproduct
{
    function __construct()
    {
        $this->multiple = true;
        $this->quantity = true;
    }
}