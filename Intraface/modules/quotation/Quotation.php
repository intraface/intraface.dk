<?php
/**
 * @package Intraface_Quotation
 */
class Quotation extends Debtor
{

    function Quotation($kernel, $id = 0)
    {
        parent::__construct($kernel, 'quotation', $id);
    }

}

?>