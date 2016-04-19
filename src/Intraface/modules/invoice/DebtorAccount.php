<?php
class DebtorAccount extends Intraface_Standard
{
    
    /**
     * @var object debtor or reminder
     */
    private $object;
    
    /**
     * @var object error
     */
    public $error;
    
    /**
     * @var string what this is an account for. Either invoice or reminder
     */
    public $account_for;

    function __construct($object)
    {

        if (!is_object($object)) {
            throw new Exception('DebtorAccount needs debtor or reminder as parameter');
            return false;
        }

        $this->account_for = strtolower(get_class($object));
        if (!in_array($this->account_for, array('invoice', 'reminder'))) {
            throw new Exception('Debtor account can only be account for either invoice or reminder. Is '.$this->account_for);
            return false;
        }
        
        $this->object = $object;
        $this->error = $object->error;
        
    }
    
    /**
     * returns Payment object
     *
     * @return object Payment
     */
    private function getPayment()
    {
        require_once 'Intraface/modules/invoice/Payment.php';
        return new Payment($this->object);
    }
    
    /**
     * returns depreciation object
     *
     * @return object Depreciation
     */
    private function getDepreciation()
    {
        require_once 'Intraface/modules/invoice/Depreciation.php';
        return new Depreciation($this->object);
    }
    
    /**
     * Returns Credit note object
     *
     * @return object creditnote
     */
    private function getCreditNote()
    {
        require_once 'Intraface/modules/invoice/CreditNote.php';
        return new CreditNote($this->object->kernel);
    }
    
    
    /**
     * returns payments, credit_notes and depreciations for an invoice or reminder
     *
     * @return array payments, credit_notes and depreciations
     */
    function getList()
    {
    
        // payments
        $payments = $this->getPayment()->getList();
        
        // depreciations
        $depreciations = $this->getDepreciation()->getList();
        
        // Hent kreditnotaer. Ikke hvis det er en reminder. Den kan ikke krediteres.
        if ($this->account_for == "invoice") {
            $credit_note = $this->getCreditNote();
            $credit_note->getDBQuery()->setCondition("where_from = 5 AND where_from_id = ".$this->object->get("id"));
            $credit_note->getDBQuery()->setSorting("this_date");
            // Det er ret kr�vende at k�re debtor->getList(), m�ske det burde g�res med direkte sql-udtr�k.
            $credit_notes = $credit_note->getList();
        } else {
            $credit_notes = array();
        }
        
        $value = array();
        $pay = 0; // payment
        $pay_max = count($payments);
        $dep = 0; // depreciations
        $dep_max = count($depreciations);
        $cre = 0; // credit_note
        $cre_max = count($credit_notes);
        
        $i = 0;
        while ($pay < $pay_max || $cre < $cre_max || $dep < $dep_max) {
            $date['payment'] = (!empty($payments[$pay]["payment_date"])) ? strtotime($payments[$pay]["payment_date"]) : 0;
            $date['depreciation'] = (!empty($depreciations[$dep]["payment_date"])) ? strtotime($depreciations[$dep]["payment_date"]) : 0;
            $date['credit_note'] = (!empty($credit_notes[$cre]["this_date"])) ? strtotime($credit_notes[$cre]["this_date"]) : 0;
            
            $date = array_filter($date); // removes items with 0
            if (count($date) == 0) {
                throw new Exception('Problem in finding the next entry!');
                return false;
            }
            asort($date); // sorts the array with the smallest first
            $next = each($date); // takes the first entry and converts to array.
            
            switch ($next['key']) {
                case 'payment':
                    $value[$i] = $payments[$pay];
                    $value[$i]["date"] = $payments[$pay]["payment_date"];
                    $value[$i]["dk_date"] = $payments[$pay]["dk_payment_date"];
                    $pay++;
                    break;
                case 'depreciation':
                    $value[$i] = $depreciations[$dep];
                    $value[$i]["date"] = $depreciations[$dep]["payment_date"];
                    $value[$i]["dk_date"] = $depreciations[$dep]["dk_payment_date"];
                    $dep++;
                    break;
                case 'credit_note':
                    $value[$i] = $credit_notes[$cre];
                    $value[$i]["type"] = "credit_note";
                    $value[$i]["date"] = $credit_notes[$cre]["this_date"];
                    $value[$i]["dk_date"] = $credit_notes[$cre]["dk_this_date"];
                    if ($credit_notes[$cre]["description"] != "") {
                        $value[$i]["description"] = $credit_notes[$cre]["description"];
                    } else {
                        $value[$i]["description"] = "[Ingen beskrivelse]";
                    }
                    $value[$i]["amount"] = $credit_notes[$cre]["total"];
                    $cre++;
                    break;
                default:
                    throw new Exception('Invalid next type "'.$next['key'].'"!');
                    return false;
            }
            $i++;
        }

        return $value;
    }
}
