<?php
/**
 * @package Intraface_Order
 */

require_once 'Intraface/modules/debtor/Debtor.php';

class Order extends Debtor
{
    function __construct($kernel, $id = 0)
    {
        Debtor::__construct($kernel, 'order', $id);
    }
}
