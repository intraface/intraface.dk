<?php
class FakeDebtor
{
    function __construct()
    {
        $this->values = array(
            'id' => 1,
            'locked' => 0,
            'type' => 'invoice',
            'dk_this_date' => '2007-10-10',
            'due_date' => '2007-10-10',
            'dk_due_date' => '2007-10-10',
            'intranet_address_id' => 1,
            'number' => 1,
            'message' => '',
            'round_off' => '',
            'total' => 2125,
            'payment_total' => 0,
            'payment_online' => 0,
            'girocode' => '',
            'payment_method' => 2);
    }

    function getItems()
    {
        $item[0] = array(
            'id' => 1,
            'name' => 'product 1',
            'number' => 1,
            'quantity' => 1,
            'unit' => 'unit',
            'price' => new Fake_Ilib_Variable_Float(100),
            'description' => 'test product 1',
            'vat' => 1,
            'amount' => 125);
        $item[1] = array(
            'id' => 2,
            'name' => 'product 2',
            'number' => 2,
            'quantity' => 10,
            'unit' => 'days',
            'price' => new Fake_Ilib_Variable_Float(200),
            'description' => 'test product 2',
            'vat' => 0,
            'amount' => 2000);
        return $item;
    }

    function get($key)
    {
        return $this->values[$key];
    }

    function getIntranetAddress()
    {
        return new Stub_Address();
    }

    function getPaymentInformation()
    {
        return array('bank_name' => 'SparNord', 'bank_reg_number' => '1243', 'bank_account_number' => '12312345678', 'giro_account_number' => '112321321');
    }

    function getContactInformation()
    {
        return array('email' => 'test@intraface.dk', 'contact_name' => 'Lars Olesen');
    }

    function getInvoiceText()
    {
        return 'Ja, det kan du tro, at der er en masse at fortaelle.';
    }

    function getCurrency()
    {
        return false;
    }
}
