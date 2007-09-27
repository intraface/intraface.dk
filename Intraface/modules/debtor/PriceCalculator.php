<?php

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
        foreach ($this->items as $item)
        {
            $price += $item->getPrice();
        }
        return $price;
    }
}