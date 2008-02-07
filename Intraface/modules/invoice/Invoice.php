<?php
/**
 * Invoice
 *
 * @package Intraface_Invoice
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */

require_once 'Intraface/modules/debtor/Debtor.php';

class Invoice extends Debtor
{
    public $payment;

    function __construct(& $kernel, $id = 0)
    {
        parent::__construct($kernel, 'invoice', $id);
    }

    function setStatus($status)
    {

        if($status == 'cancelled') {
            trigger_error('En faktura kan ikke annulleres!', E_USER_ERROR);
        }

        $is_true = parent::setStatus($status);

        return $is_true;
    }

    function anyDue($contact_id)
    {
        //$invoice = new Invoice($this->kernel);
        $db = new DB_Sql;
        $sql = "SELECT id FROM debtor
            WHERE type=3 AND status=1 AND due_date < NOW() AND intranet_id = ".$this->kernel->intranet->get("id")." AND contact_id = " . $contact_id;
        $db->query($sql);

        return $db->numRows();
    }

    /**
     * Sørger for at sætte status til eller fra executed, ved registrering af betaling og kreditering
     */

    function updateStatus()
    {

        if(round($this->get("arrears")) == 0) {
            $go_status = "executed";
        } else {
            $go_status = "sent";
        }

        if($go_status != $this->get("status") && ($this->get("status") == "sent" || $this->get("status") == "executed")) {
            $this->setStatus($go_status);
        }
        return true;
    }

    /**
     * returns DebtorAccount object
     *
     * @return object DebtorAccount
     */
    public function getDebtorAccount()
    {
        require_once 'Intraface/modules/invoice/DebtorAccount.php';
        return new DebtorAccount($this);
    }

    /*
     * removed 22/1 2008
    function getPayments($to_date = "")
    {

        $this->payment = new Payment($this);
        $this->payment->dbquery->setFilter("to_date", $to_date);
        $payments = $this->payment->getList();
        $payment_types = $this->payment->getTypes();

        foreach($payment_types AS $type) {
            $payment[$type] = 0;
        }

        $payment["credit_note"] = 0;
        $payment['total'] = 0;

        for($i = 0, $max = count($payments); $i < $max; $i++) {
            $payment[$payments[$i]["type"]] += $payments[$i]["amount"];
            $payment["total"] += $payments[$i]["amount"];
        }

        return $payment;
    }
    */

    function delete()
    {
        if($this->get("status") == "created") {
            return Debtor::delete();
        } else {
            $this->error->set('Fakturaen må ikke være sendt eller annulleret');
            return false;
        }
    }

    /**
     * Returns whether the invoice is ready for state
     *
     * @param object year
     * @param string 'check_product' if it should check that the products have a valid account, or 'skip_check_products'
     * @return boolean true or false
     */
    function readyForState($year, $check_products = 'check_products')
    {
        if(!is_object($year)) {
            trigger_error('First parameter to readyForState needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if(!in_array($check_products, array('check_products', 'skip_check_products'))) {
            trigger_error('Second paramenter in Invice->readyForState should be either "check_products" or "skip_check_products"', E_USER_ERROR);
            return false;
        }

        if (!$year->readyForState()) {
            $this->error->set('Regnskabåret er ikke klar til bogføring');
            return false;
        }


        if ($this->type != 'invoice') {
            $this->error->set('Du kan kun bogføre fakturaer');
            return false;
        }

        if($this->isStated()) {
            $this->error->set('Fakturaen er allerede bogført');
            return false;
        }

        if($this->get('status') != 'sent' && $this->get('status') != 'executed') {
            $this->error->set('Fakturaen skal være sendt eller afsluttet for at den kan bogføres');
            return false;
        }

        $debtor_account = new Account($year, $year->getSetting('debtor_account_id'));
        if($debtor_account->get('id') == 0 || $debtor_account->get('type') != 'balance, asset') {
            $this->error->set('Ugyldig debitor konto sat i regnskabsindstillingerne.');
            return false;
        }

        if($check_products == 'check_products') {
            $this->loadItem();
            $items = $this->item->getList();
            for ($i = 0, $max = count($items); $i < $max; $i++) {
                $product = new Product($this->kernel, $items[$i]['product_id']);
                if ($product->get('state_account_id') == 0) {
                    $this->error->set('Produktet ' . $product->get('name') . ' ved ikke hvor den skal bogføres');
                } else {
                    require_once 'Intraface/modules/accounting/Account.php';
                    $account = Account::factory($year, $product->get('state_account_id'));
                    if($account->get('id') == 0 || $account->get('type') != 'operating') {
                        $this->error->set('Ugyldig konto for bogføring af produktet ' . $product->get('name'));
                    }
                }
            }
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * State invoice
     *
     * @param object year stating year
     * @param integer voucher_number
     * @param string voucher_date
     * @return boolean true or false
     */
    function state($year, $voucher_number, $voucher_date, $translation)
    {
        if(!is_object($year)) {
            trigger_error('First parameter to state needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if(!is_object($translation)) {
            trigger_error('4th parameter to state needs to be a translation object!', E_USER_ERROR);
            return false;
        }

        $validator = new Validator($this->error);
        if($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        $validator->isNumeric($voucher_number, 'Ugyldigt bilagsnummer', 'greater_than_zero');

        if ($this->error->isError()) {
            return false;
        }

        if (!$this->readyForState($year)) {
            $this->error->set('Faktura er ikke klar til bogføring');
            return false;
        }

        // hente alle produkterne på debtor
        $this->loadItem();
        $items = $this->item->getList();

        $text = $translation->get('invoice').' #'.$this->get('number');

        require_once 'Intraface/modules/accounting/Voucher.php';
        require_once 'Intraface/modules/accounting/Account.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $voucher->save(array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'text' => $text,
            'invoice_number' =>  $this->get('number')
        ));


        $total = 0;
        foreach($items AS $item) {

            // produkterne
            // bemærk at denne går ud fra at alt skal overføres til debtorkontoen som standard
            $product = new Product($this->kernel, $item['product_id']);
            $credit_account = Account::factory($year, $product->get('state_account_id'));
            $credit_account_number = $credit_account->get('number');
            $debet_account = new Account($year, $year->getSetting('debtor_account_id'));
            $debet_account_number = $debet_account->get('number');
            $voucher = Voucher::factory($year, $voucher_number);

            $amount = $item['quantity'] * $item['price'];

            // hvis beløbet er mindre end nul, skal konti byttes om og beløbet skal gøres positivt
            if ($amount < 0) {
                $debet_account_number = $credit_account->get('number');
                $credit_account_number = $debet_account->get('number');
                $amount = abs($amount);
            }

            $input_values = array(
                'voucher_number' => $voucher_number,
                'invoice_number' => $this->get('number'),
                'date' => $voucher_date,
                'amount' => number_format($amount, 2, ",", "."),
                'debet_account_number' => $debet_account_number,
                'credit_account_number' => $credit_account_number,
                'vat_off' => 1,
                'text' => $text . ' - ' . $item['name']
            );
            if ($credit_account->get('vat_off') == 0) {
                $total += $item["quantity"] * $item["price"];
            }

            if (!$voucher->saveInDaybook($input_values, true)) {
                $this->error->merge($voucher->error->getMessage());
            }
        }
        // samlet moms på fakturaen
        $voucher = Voucher::factory($year, $voucher_number);
        $credit_account = new Account($year, $year->getSetting('vat_out_account_id'));
        $debet_account = 	new Account($year, $year->getSetting('debtor_account_id'));
        $input_values = array(
                'voucher_number' => $voucher_number,
                'invoice_number' => $this->get('number'),
                'date' => $voucher_date,
                'amount' => number_format($total * $this->kernel->setting->get('intranet', 'vatpercent') / 100, 2, ",", "."), // opmærksom på at vat bliver rigtig defineret
                'debet_account_number' => $debet_account->get('number'),
                'credit_account_number' => $credit_account->get('number'),
                'vat_off' => 1,
                'text' => $text . ' - ' . $credit_account->get('name')
        );


        if (!$voucher->saveInDaybook($input_values, true)) {
            $this->error->merge($voucher->error->getMessage());
        }

        require_once 'Intraface/modules/accounting/VoucherFile.php';
        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => $text, 'belong_to'=>'invoice','belong_to_id'=>$this->get('id')))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
        }

        if($this->error->isError()) {
            $this->error->set('Der er opstået en fejl under bogføringen af fakturaen. Det kan betyde at dele af den er bogført, men ikke det hele. Du bedes manuelt tjekke bilaget');
            // I am not quite sure if the invoice should be set as stated, but it can give trouble to state it again, if some of it was stated...
            $this->setStated($voucher->get('id'), $this_date->get());
            return false;
        }

        $this->setStated($voucher->get('id'), $this_date->get());
        $this->load();
        return true;

    }

    /**
     * Implements the visitor pattern
     * @param  object  Visitor
     * @return void
     */
    function accept(Visitor $visitor)
    {
        $visitor->visit($this);
    }

}