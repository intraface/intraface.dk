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
    protected $dbquery;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->error = new Intraface_Error;
        $this->dbquery = $this->getDBQuery();
    }

    function findById($id)
    {
        $db = new DB_Sql;
        $db->query('SELECT * FROM invoice_payment WHERE id = ' . $id);

        if (!$db->nextRecord()) {
            return false;
        }

        require_once 'Intraface/modules/invoice/Payment.php';

        $debtor_gateway = new Intraface_modules_debtor_DebtorGateway($this->kernel);
        $debtor = $debtor_gateway->findById((int)$db->f('payment_for_id'));

        $payment = new Payment($debtor, $id);
        return $payment;
    }

    function findByType()
    {

    }

    function getDBQuery()
    {
        if (is_object($this->dbquery)) {
            return $this->dbquery;
        }
        $this->dbquery = new Intraface_DBQuery($this->kernel, "invoice_payment", "intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
    }

    function findAll()
    {
        $db = new DB_Sql;
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
        $db = $this->dbquery->getRecordset("id, amount, payment_for, type, description, payment_date, payment_for_id, DATE_FORMAT(payment_date, '%d-%m-%Y') AS dk_payment_date, date_stated, voucher_id", "", false);
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
            $payment_for_types = $this->getPaymentForTypes();
            $payment[$i]['payment_for'] = $payment_for_types[$db->f('payment_for')];
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