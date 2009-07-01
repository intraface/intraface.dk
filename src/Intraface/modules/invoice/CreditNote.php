<?php
/**
 * Invoice
 *
 * @package Intraface_Invoice
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/modules/debtor/Debtor.php';

class CreditNote extends Debtor
{
    function __construct($kernel, $id = 0)
    {
        parent::__construct($kernel, 'credit_note', $id);
    }

    function setStatus($status)
    {

        $return = parent::setStatus($status);
        if ($status == "sent") {
            // Er den sendt, bliver den også låst
            return parent::setStatus("executed");
        } else {
            return $return;
        }
    }

    function delete()
    {
        if ($this->get("where_from") == "invoice" && $this->get("where_from_id") != 0) {
            $invoice = parent::factory($this->kernel, (int)$this->get("where_from_id"));
        }
        parent::delete();
        if (isset($invoice)) {
            $invoice->updateStatus();
        }
    }

    function readyForState($year, $check_products = 'check_products')
    {
        if (!is_object($year)) {
            trigger_error('First parameter to readyForState needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if (!in_array($check_products, array('check_products', 'skip_check_products'))) {
            trigger_error('Second paramenter in creditnote->readyForState should be either "check_products" or "skip_check_products"', E_USER_ERROR);
            return false;
        }

        if (!$year->readyForState($this->get('this_date'))) {
            $this->error->set('Regnskabåret er ikke klar til bogføring');
            return false;
        }


        if ($this->type != 'credit_note') {
            $this->error->set('Du kan kun bogføre kreditnotaer');
            return false;
        }

        if ($this->isStated()) {
            $this->error->set('Kreditnotaen er allerede bogført');
            return false;
        }

        if ($this->get('status') != 'sent' && $this->get('status') != 'executed') {
            $this->error->set('Kreditnotaen skal være sendt eller afsluttet for at den kan bogføres');
            return false;
        }
        
        $return = true;

        if ($check_products == 'check_products') {
            $this->loadItem();
            $items = $this->item->getList();
            for ($i = 0, $max = count($items); $i < $max; $i++) {
                $product = new Product($this->kernel, $items[$i]['product_id']);
                if ($product->get('state_account_id') == 0) {
                    $this->error->set('Produktet ' . $product->get('name') . ' ved ikke hvor den skal bogføres');
                }
                else {
                    require_once 'Intraface/modules/accounting/Account.php';
                    $account = Account::factory($year, $product->get('state_account_id'));
                    if ($account->get('id') == 0 || $account->get('type') != 'operating') {
                        $this->error->set('Ugyldig konto for bogføring af produktet ' . $product->get('name'));
                        $return = false;
                    }
                }
            }
        }

        return $return;

    }

    function state($year, $voucher_number, $voucher_date, $translation)
    {
        if (!is_object($year)) {
            trigger_error('First parameter to state needs to be a Year object!', E_USER_ERROR);
            return false;
        }

        if (!is_object($translation)) {
            trigger_error('4th parameter to state needs to be a translation object!', E_USER_ERROR);
            return false;
        }

        $text = $translation->get('credit note');

        $validator = new Intraface_Validator($this->error);
        if ($validator->isDate($voucher_date, "Ugyldig dato")) {
            $this_date = new Intraface_Date($voucher_date);
            $this_date->convert2db();
        }

        if ($this->error->isError()) {
            return false;
        }

        if (!$this->readyForState($year)) {
            $this->error->set('Kreditnotaen er ikke klar til bogføring');
            return false;
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
            'text' => 'Kreditnota #' . $this->get('number')
        ));


        $total_with_vat = 0;
        foreach ($items AS $item) {

            // produkterne
            // bemærk at denne går ud fra at alt skal overføres til debtorkontoen som standard
            $product = new Product($this->kernel, $item['product_id']);
            $debet_account = Account::factory($year, $product->get('state_account_id'));
            $debet_account_number = $debet_account->get('number');
            $credit_account = new Account($year, $year->getSetting('debtor_account_id'));
            $credit_account_number = $credit_account->get('number');
            $voucher = Voucher::factory($year, $voucher_number);

            $amount = $item['quantity'] * $item['price']->getAsIso(2);

            // hvis beløbet er mindre end nul, skal konti byttes om og beløbet skal gøres positivt
            if ($amount < 0) {
                $debet_account_number = $credit_account->get('number');
                $credit_account_number = $debet_account->get('number');
                $amount = abs($amount);
            }

            $input_values = array(
                'voucher_number' => $voucher_number,
                'reference' => $this->get('number'),
                'date' => $voucher_date,
                'amount' => number_format($amount, 2, ",", "."),
                'debet_account_number' => $debet_account_number,
                'credit_account_number' => $credit_account_number,
                'vat_off' => 1,
                'text' => $text.' #' . $this->get('number') . ' - ' . $item['name']
            );
            if ($debet_account->get('vat') == 'out') {
                $total_with_vat += $item["quantity"] * $item["price"]->getAsIso(2);
            }

            if (!$voucher->saveInDaybook($input_values, true)) {
                $voucher->error->view();
            }
        }
        
        // samlet moms på fakturaen
        if ($total_with_vat != 0) {
            $voucher = Voucher::factory($year, $voucher_number);
            $debet_account = new Account($year, $year->getSetting('vat_out_account_id'));
            $credit_account = new Account($year, $year->getSetting('debtor_account_id'));
            $input_values = array(
                    'voucher_number' => $voucher_number,
                    'reference' => $this->get('number'),
                    'date' => $voucher_date,
                    'amount' => number_format($total_with_vat * $this->kernel->setting->get('intranet', 'vatpercent') / 100, 2, ",", "."), // opmærksom på at vat bliver rigtig defineret
                    'debet_account_number' => $debet_account->get('number'),
                    'credit_account_number' => $credit_account->get('number'),
                    'vat_off' => 1,
                    'text' => $text.' #' . $this->get('number') . ' - ' . $debet_account->get('name')
            );
            if (!$voucher->saveInDaybook($input_values, true)) {
                $this->error->merge($voucher->error->getMessage());
            }
        }

        require_once 'Intraface/modules/accounting/VoucherFile.php';
        $voucher_file = new VoucherFile($voucher);
        if (!$voucher_file->save(array('description' => $text.' #' . $this->get('number'), 'belong_to'=>'credit_note','belong_to_id'=>$this->get('id')))) {
            $this->error->merge($voucher_file->error->getMessage());
            $this->error->set('Filen blev ikke overflyttet');
        }

        if ($this->error->isError()) {
            $this->error->set('Der er opstået en fejl under bogføringen af kreditnotaen. Det kan betyde at dele af den er bogført, men ikke det hele. Du bedes manuelt tjekke bilaget');
            // I am not quite sure if the credit note should be set as stated, but it can give trouble to state it again, if some of it was stated...
            $this->setStated($voucher->get('id'), $this_date->get());
            return false;
        }

        $this->setStated($voucher->get('id'), $this_date->get());
        $this->load();
        return true;

    }
}