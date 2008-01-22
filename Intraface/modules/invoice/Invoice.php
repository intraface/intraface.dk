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

    var $payment;

    function __construct(& $kernel, $id = 0)
    {
        Debtor::__construct($kernel, 'invoice', $id);
    }

    function setStatus($status)
    {

        if($status == 'cancelled') {
            trigger_error('En faktura kan ikke annulleres!', E_USER_ERROR);
        }

        $is_true = Debtor::setStatus($status);

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

        $payment = $this->getPayments();
        // print($payment["total"].' == '.$this->get("total"));
        if($payment["total"] == $this->get("total")) {
            $go_status = "executed";
        }
        else {
            $go_status = "sent";
        }

        if($go_status != $this->get("status") && ($this->get("status") == "sent" || $this->get("status") == "executed")) {
            $this->setStatus($go_status);
        }
        return true;
    }

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

    function delete()
    {
        if($this->get("status") == "created") {
            return Debtor::delete();
        } else {
            $this->error->set('Fakturaen må ikke være sendt eller annulleret');
            return false;
        }
    }

    function readyForState($check_products = 'check_products')
    {
        if(!in_array($check_products, array('check_products', 'skip_check_products'))) {
            trigger_error('First paramenter in Invice->readyForState should be either "check_products" or "skip_check_products"', E_USER_ERROR);
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

        if($check_products == 'check_products') {
            $this->loadItem();
            $items = $this->item->getList();
            for ($i = 0, $max = count($items); $i < $max; $i++) {
                $product = new Product($this->kernel, $items[$i]['product_id']);
                if ($product->get('state_account_id') == 0) {
                    $this->error->set('Produktet ' . $product->get('name') . ' ved ikke hvor den skal bogføres');
                }
            }
        }
        
        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function state($year, $voucher_number, $voucher_date)
    {
        $validator = new Validator($this->error);
        if($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        if ($this->error->isError()) {
            return false;
        }


        // FIXME - der skal laves tjek på datoen
        if ($this->isStated()) {
            $this->error->set('Allerede bogført');
            return false;
        }
        if (!$this->readyForState()) {
            $this->error->set('Faktura er ikke klar til bogføring');
            return false;
        }
        
        if (!$year->readyForState()) {
            $this->error->set('Regnskabåret er ikke klar til bogføring');
            return false;
        }
        
        if ($this->get('type') != 'invoice') {
            $this->error->set('Ikke en faktura');
            return false;
        }

        if (!$this->kernel->user->hasModuleAccess('accounting')) {
            trigger_error('Ikke rettigheder til at bogføre', E_USER_ERROR);
        }

        
        // hente alle produkterne på debtor
        $this->loadItem();
        $items = $this->item->getList();

        require_once 'Intraface/modules/accounting/Voucher.php';
        require_once 'Intraface/modules/accounting/Account.php';
        $voucher = Voucher::factory($year, $voucher_number);
        $voucher->save(array(
            'voucher_number' => $voucher_number,
            'date' => $voucher_date,
            'text' => 'Faktura #' . $this->get('number'),
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
                'text' => 'Faktura #' . $this->get('number') . ' - ' . $item['name']
            );
            if ($credit_account->get('vat_off') == 0) {
                $total += $item["quantity"] * $item["price"];
            }

            if (!$voucher->saveInDaybook($input_values, true)) {
                $this->error->merge($voucher->error->getMessage());
            }
        }
        // samlet moms på fakturaen
        // opmærksom på at momsbeløbet her er hardcoded - og det bør egentlig tages fra fakturaen?
        $voucher = Voucher::factory($year, $voucher_number);
        $account = new Account($year, $year->getSetting('vat_out_account_id'));
        $debet_account = 	new Account($year, $year->getSetting('debtor_account_id'));
        $input_values = array(
                'voucher_number' => $voucher_number,
                'invoice_number' => $this->get('number'),
                'date' => $voucher_date,
                'amount' => number_format($total * $this->kernel->setting->get('intranet', 'vatpercent') / 100, 2, ",", "."), // opmærksom på at vat bliver rigtig defineret
                'debet_account_number' => $debet_account->get('number'),
                'credit_account_number' => $account->get('number'),
                'vat_off' => 1,
                'text' => 'Faktura #' . $this->get('number') . ' - ' . $account->get('name')
        );


        if (!$voucher->saveInDaybook($input_values, true)) {
            $this->error->merge($voucher->error->getMessage());
        }

        require_once 'Intraface/modules/accounting/VoucherFile.php';
        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => 'Faktura ' . $this->get('number'), 'belong_to'=>'invoice','belong_to_id'=>$this->get('id')))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
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

?>