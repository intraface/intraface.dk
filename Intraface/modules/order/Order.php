<?php
/**
 * @package Intraface_Order
 */
class Order extends Debtor
{
    function __construct($kernel, $id = 0)
    {
        Debtor::__construct($kernel, 'order', $id);
    }
}
