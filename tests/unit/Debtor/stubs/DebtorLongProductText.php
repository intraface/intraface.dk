<?php
require_once 'Debtor.php';


class FakeDebtorLongProductText extends FakeDebtor
{
    function __construct()
    {
        parent::__construct();
    }
    
    
    function getItems() {
        require_once 'Ilib/Variable/Float.php';
        $item[0] = array('id' => 1, 'name' => 'product 1 is with quite a long name, to long to one line, that is for sure', 'number' => 1, 'quantity' => 1, 'unit' => 'unit', 'price' => new Ilib_Variable_Float(100), 'description' => 'And this some description about the product, which also fills more than one line.', 'vat' => 1, 'amount' => 1);
        $item[1] = array('id' => 2, 'name' => 'product 2 has also a long name, to long to one line, nobody can question that', 'number' => 2, 'quantity' => 10, 'unit' => 'days', 'price' => new Ilib_Variable_Float(200), 'description' => 'And this some description about the product, which also fills more than one line.', 'vat' => 1, 'amount' => 1000);
        return $item;
        
    }
}

?>