<?php
/**
 * @package Intraface_Order
 */

class Order extends Debtor {

    function Order(& $kernel, $id = 0) {
        Debtor::Debtor($kernel, 'order', $id);
    }

}

?>