<?php
/**
 * Invoice
 *
 * @package Intraface_Invoice
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */

class Invoice extends Debtor {

    var $payment;

    function __construct(& $kernel, $id = 0) {
        Debtor::__construct($kernel, 'invoice', $id);
    }

    function setStatus($status) {

        if($status == 'cancelled') {
            trigger_error('En faktura kan ikke annulleres!', E_USER_ERROR);
        }

        $is_true = Debtor::setStatus($status);

        /**
         * Midlertidig HACK:
         * Da status nu godt kan gå fra executed til sent, er vi nødt til at sikre os at den ikke tidligere har været executed
         *
         * NU VÆK, nyt stock erstatter denne.
         */
        /*
        if($is_true && $this->get("type") == 3 && $status == "executed" && $this->get("date_executed") == "0000-00-00") {

            if($this->kernel->intranet->hasModuleAccess("stock")) {
                $main_stock = $this->kernel->useModule("stock", true);

                $this->loadItem();
                $items = $this->item->getList();

                foreach ($items AS $item) {
                    $product = new Product($this->kernel, $item['product_id']);
                    $stock = new Stock($product);
                    $stock->reduce($item['quantity']);
                }
            }
        }
        */

        return $is_true;
    }

    function anyDue($contact_id) {
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

    function updateStatus() {

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

    function getPayments($to_date = "") {

        $this->payment = new Payment($this);
        $this->payment->dbquery->setFilter("to_date", $to_date);
        $payments = $this->payment->getList();

        $module_invoice = $this->kernel->useModule('invoice');
        $payment_types = $module_invoice->getSetting('payment_type');

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

    function delete() {
        if($this->get("status") == "created") {
            return Debtor::delete();
        }
        else {
            $this->error->set('Fakturaen må ikke være sendt eller annulleret');
            return false;
        }
    }

    function invoiceReadyForState() {
        if (!$this->readyForState()) {
            return 0;
        }
        if ($this->type != 'invoice') {
            $this->error->set('Du kan kun bogføre fakturaer');
            return 0;
        }

        $this->loadItem();
        $items = $this->item->getList();
        for ($i = 0, $max = count($items); $i < $max; $i++) {
            $product = new Product($this->kernel, $items[$i]['product_id']);
            if ($product->get('state_account_id') == 0) {
                $this->error->set('Produktet ' . $product->get('name') . ' ved ikke hvor den skal bogføres');
            }
        }
        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function state($year, $voucher_number, $voucher_date) {
        $validator = new Validator($this->error);
        if($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        if ($this->error->isError()) {
            return 0;
        }


        // FIXME - der skal laves tjek på datoen
        if ($this->isStated()) {
            $this->error->set('Allerede bogført');
            return 0;
        }
        if (!$this->invoiceReadyForState()) {
            $this->error->set('Ikke klar til bogføring');
            return 0;
        }
        if ($this->get('type') != 'invoice') {
            $this->error->set('Ikke en faktura');
            return 0;
        }

        if (!$this->kernel->user->hasModuleAccess('accounting')) {
            trigger_error('Ikke rettigheder til at bogføre', E_USER_ERROR);
        }

        $this->kernel->useModule('accounting');



        // hente alle produkterne på debtor
        $this->loadItem();
        $items = $this->item->getList();

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
                $voucher->error->view();
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
            $voucher->error->view();
        }

        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => 'Faktura ' . $this->get('number'), 'belong_to'=>'invoice','belong_to_id'=>$this->get('id')))) {
            $voucher_file->error->view();
            $this->error->set('Filen blev ikke overflyttet');
        }


        $this->setStated($voucher->get('id'), $this_date->get());

        $this->load();


        return 1;

    }

    /**
     * Implements the visitor pattern
     * @param  object  Visitor
     * @return void
     */
    function accept(Visitor $visitor) {
        $visitor->visit($this);
    }


}

?>