<?php
/**
 * @package Intraface_Debtor
 */
class Intraface_Debtor_PriceCalculator
{
    protected $items;

    function __construct($items)
    {
        $this->items = $items;
    }

    function calculate()
    {
        $price = 0;
        foreach ($this->items as $item) {
            $price += $item->getPrice();
        }
        return $price;
    }
    /*
    function getVat()
    {
        $vat = 0;
        foreach ($this->items as $item) {
            if (isset($item['vat']) AND $item['vat'] == 1) {
            }
            $price += $item->getPrice();
        }
        return $price;
    }
    */
}
