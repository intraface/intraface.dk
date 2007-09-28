<?php
/**
 * @package Intraface_Quotation
 */
class Quotation extends Debtor {

    function Quotation(& $kernel, $id = 0) {
        Debtor::Debtor($kernel, 'quotation', $id);
    }

}

?>