<?php
/**
 * Used to register depreciation to invoices and reminder fee
 * @package Intraface_Invoice
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @version: 1
 */

require_once 'Intraface/modules/invoice/Payment.php';

class Depreciation extends Payment
{
    /**
     * Constructor
     *
     * @param object invoice or reminder
     * @param integer id [optional]
     *
     */
    function __construct($object, $id = 0)
    {
        parent::__construct($object, $id);
    }

    /**
     * update a depreciation
     *
     * @param array input [optional] data to save. If no input given only updates the given object by object->updateStatus
     * @return boolean true or false
     */
    function update($input = "")
    {
        if (is_array($input)) {
            $input['type'] = -1;
        }
        return parent::update($input);
    }

    /**
     * States the payment i the given year
     *
     * @param object $year Accounting Year object
     * @param integer $voucher_number
     * @param string $voucher_date
     * @param integer $state_account_number
     *
     * @return boolean true on succes or false.
     */
    public function state($year, $voucher_number, $voucher_date, $state_account_number, $translation)
    {
        if (!is_object($year)) {
            trigger_error('First parameter to state needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if ($this->payment_for_type_id == 0) {
            trigger_error('Invalid paymet_for_type_id in Payment->state', E_USER_ERROR);
            return false;
        }

        if (!is_object($translation)) {
            trigger_error('5th parameter to state needs to be a translation object!', E_USER_ERROR);
            return false;
        }

        $validator = new Intraface_Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        $validator->isNumeric($voucher_number, 'Ugyldigt bilagsnummer', 'greater_than_zero');
        $validator->isNumeric($state_account_number, 'Ugyldig bogføringskonto', 'greater_than_zero');

        if (!$this->readyForState()) {
            return false;
        }

        if (!$year->readyForState()) {
            $this->error->merge($year->error->getMessage());
            return false;
        }

        // this should be a method in Year instead
        require_once 'Intraface/modules/accounting/Account.php';
        $credit_account = new Account($year, $year->getSetting('debtor_account_id'));
        if (!$credit_account->validForState()) {
            $this->error->set('Den gemte debitorkonto er ikke gyldig til bogføring');
            return false;
        }
        $credit_account_number = $credit_account->get('number');

        $debet_account = Account::factory($year, $state_account_number);
        if (!$debet_account->validForState()) {
            $this->error->set('Den valgte konto for bogføring er ikke gyldig');
            return false;
        }
        $debet_account_number = $debet_account->get('number');

        require_once 'Intraface/modules/accounting/Voucher.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $amount = $this->get('amount');

        // hvis beløbet er mindre end nul, skal konti byttes om og beløbet skal gøres positivt
        if ($amount < 0) {
            $debet_account_number = $credit_account->get('number');
            $credit_account_number = $debet_account->get('number');
            $amount = abs($amount);
        }

        $types = $this->getPaymentForTypes();
        // translation is needed!
        $text = $translation->get('depreciation for').' '.$translation->get($types[$this->payment_for_type_id]).' #'.$this->payment_for->get('number');

        $input_values = array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'amount' => number_format($amount, 2, ",", "."),
            'debet_account_number' => $debet_account_number,
            'credit_account_number' => $credit_account_number,
            'text' => $text
        );

        if (!$voucher->saveInDaybook($input_values, true)) {
            $this->error->merge($voucher->error->getMessage());
            return false;
        }

        $db = new DB_sql;
        $db->query("UPDATE invoice_payment SET date_stated = NOW(), voucher_id = ".$voucher->get('id'));

        $this->load();
        return true;
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
            -1=>'depreciation'
        );
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