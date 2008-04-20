<?php

require_once 'Intraface/modules/debtor/Debtor.php';

/**
 * @package Intraface_Quotation
 */
class Quotation extends Debtor
{

    function __construct($kernel, $id = 0)
    {
        parent::__construct($kernel, 'quotation', $id);
    }

}