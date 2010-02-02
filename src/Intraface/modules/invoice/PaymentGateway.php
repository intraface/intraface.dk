<?php
/**
 * Klasse bruges til at registere betaling af faktura, og rykker(gebyr)
 * @package Intraface_Invoice
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @version: 1
 */
class Intraface_modules_invoice_PaymentGateway
{
    protected $kernel;
    protected $error;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
    }

    function findByType()
    {

    }

    function findAll()
    {
        $db = new DB_sql;
        $i = 0;
        $payment = array();
        $credit_note = array();

        if ($this->dbquery->checkFilter("to_date")) {
            $date = new Intraface_Date($this->dbquery->getFilter("to_date"));
            if ($date->convert2db()) {
                $this->dbquery->setCondition("payment_date <= \"".$date->get()."\"");
            }
        }

        $this->dbquery->setSorting("payment_date ASC");
        $db = $this->dbquery->getRecordset("id, amount, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date, date_stated, voucher_id", "", false);
        while($db->nextRecord()) {
            $payment[$i]["id"] = $db->f("id");
            $types = $this->getTypes();
            $payment[$i]["type"] = $types[$db->f('type')];
            $payment[$i]["amount"] = $db->f("amount");
            $payment[$i]["description"] = $db->f("description");
            $payment[$i]["payment_date"] = $db->f("payment_date");
            $payment[$i]["dk_payment_date"] = $db->f("dk_payment_date");
            $payment[$i]["is_stated"] = ($db->f('date_stated') > '0000-00-00');
            $payment[$i]["voucher_id"] = $db->f("voucher_id");
            $payment[$i]["payment_for_id"] = $db->f("payment_for_id");
            $i++;
        }

        return $payment;
    }


    /**
     * returns possible payment types
     *
     * @return array payment types
     *
     */
    public static function getTypes()
    {
        return array(
            0 => 'bank_transfer',
            1 => 'giro_transfer',
            2 => 'credit_card',
            3 => 'cash');
    }

    /**
     * returns the possible types payments can be for.
     *
     * @return array payment for types
     */
    private static function getPaymentForTypes()
    {
        return array(
            0 => 'manuel',
            1 => 'invoice',
            2 => 'reminder');
    }
}