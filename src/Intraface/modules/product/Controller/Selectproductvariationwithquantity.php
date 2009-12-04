<?php
class Intraface_modules_product_Controller_Selectproductvariationwithquantity extends Intraface_modules_product_Controller_Selectproductvariation
{
    function __construct()
    {

        $this->quantity = true;
        $this->multiple = true;
    }
}