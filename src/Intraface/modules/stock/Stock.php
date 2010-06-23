<?php
/**
 * @package Intraface_Stock
 */
class Stock extends Intraface_Standard
{
    private $product;
    private $product_variation_id;
    public $value;
    private $kernel;

    function __construct($product, $variation = NULL)
    {
        $this->product = $product;

        if ($variation) {
            $this->product_variation_id = $variation->getId();
        } else {
            $this->product_variation_id = 0;
        }

        if ($this->product->get("id") > 0) {
            $this->load();
        }
    }

    public function getError()
    {
        return $this->error;
    }

    function load()
    {
        $db = new DB_sql;

        // Hvorn�r var blev produktet sidst afstemt. Vi tager derfra.
        $db->query("SELECT id, quantity, adaptation_date_time, DATE_FORMAT(adaptation_date_time, '%d-%m-%Y %H:%i') AS dk_adaptation_date_time
            FROM stock_adaptation WHERE intranet_id = ".$this->product->kernel->intranet->get("id")." AND product_id = ".$this->product->get("id")." AND product_variation_id = ".$this->product_variation_id." ORDER BY adaptation_date_time DESC");
        if ($db->nextRecord()) {
            $basis = intval($db->f("quantity"));
            $basis_date = $db->f("adaptation_date_time");
            $this->value["dk_adaptation_date_time"] = $db->f('dk_adaptation_date_time');
        } else {
            $basis = 0;
            $basis_date = 0;
            $this->value["dk_adaptation_date_time"] = 'Ej afstemt';
        }

        if ($this->product->kernel->intranet->hasModuleAccess('procurement')) {
            $this->product->kernel->useModule('procurement', true); // true: vi ignorere brugeradgang.

            $procurement = new Procurement($this->product->kernel);
            $procurement->loadItem();
            $stock_in = $procurement->item->getQuantity('delivered', $this->product->get('id'), $this->product_variation_id, $basis_date);
            $this->value["on_order"] = $procurement->item->getQuantity('ordered', $this->product->get('id'), $this->product_variation_id);
        } else {
            $stock_in = 0;
            $this->value["on_order"] = 0;
        }

        if ($this->product->kernel->intranet->hasModuleAccess('invoice')) {
            $this->product->kernel->useModule('debtor', true); // true: vi ignorere brugeradgang.

            $invoice = Debtor::factory($this->product->kernel, 0, "invoice");
            $invoice->loadItem();
            $stock_out = $invoice->item->getQuantity($this->product->get('id'), $this->product_variation_id, $basis_date);
        } else {
            $stock_out = 0;
        }

        if ($this->product->kernel->intranet->hasModuleAccess('invoice')) {
            $this->product->kernel->useModule('debtor', true); // true: vi ignorere brugeradgang.

            $credit_note = Debtor::factory($this->product->kernel, 0, "credit_note");
            $credit_note->loadItem();
            $stock_out_reduced = $credit_note->item->getQuantity($this->product->get('id'), $this->product_variation_id, $basis_date);
        } else {
            $stock_out_reduced = 0;
        }

        // Reguleret
        $db->query("SELECT SUM(quantity) AS regulated
            FROM stock_regulation
            WHERE intranet_id = ".$this->product->kernel->intranet->get('id')." AND product_id = ".$this->product->get('id')." AND product_variation_id = ".$this->product_variation_id." AND regulation_date_time > \"".$basis_date."\"");
        $db->nextRecord(); // Der vil altid v�re en post
        $regulated = intval($db->f('regulated'));
        $this->value["actual_stock"] = $basis + $stock_in - $stock_out + $stock_out_reduced + $regulated;

        if ($this->product->kernel->intranet->hasModuleAccess('order')) {
            $this->product->kernel->useModule('debtor', true); // true: vi ignorere brugeradgang.
            $order = Debtor::factory($this->product->kernel, 0, "order");
            $order->loadItem();
            $this->value["reserved"] = $order->item->getQuantity($this->product->get('id'), $this->product_variation_id, $basis_date);
        } else {
            $this->value["reserved"] = 0;
        }

        if ($this->product->kernel->intranet->hasModuleAccess('invoice')) {
            $this->product->kernel->useModule('debtor', true); // true: vi ignorere brugeradgang.
            $invoice = Debtor::factory($this->product->kernel, 0, "invoice");
            $invoice->loadItem();
            $this->value["reserved"] += $invoice->item->getQuantity($this->product->get('id'), $this->product_variation_id, $basis_date, "not_sent");
        } else {
            // $this->value["reserved"] += 0;
        }

        if ($this->product->kernel->intranet->hasModuleAccess('quotation')) {
            $this->product->kernel->useModule('debtor', true); // true: vi ignorere brugeradgang.

            $quotation = Debtor::factory($this->product->kernel, 0, "quotation");
            $quotation->loadItem();
            $this->value["on_quotation"] = $quotation->item->getQuantity($this->product->get('id'), $this->product_variation_id, $basis_date);
        } else {
            $this->value["on_quotation"] = 0;
        }

        // er den her realistisk, eller skal man tage quotation med inden det udl�ber?
        $this->value['for_sale'] = $this->value['actual_stock'] - $this->value['reserved'];
    }

    /**
     * Til regulering af stock
     *
     * @param array $input description, quantity
     *
     * @return boolean
     */
    function regulate($input)
    {
        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->product->error);

        $validator->isNumeric($input['quantity'], 'Antal er ikke et gyldigt tal', 'integer');
        $validator->isString($input['description'], 'Du skal angive en beskrivelse');

        if ($this->product->error->isError()) {
            return false;
        }

        $db = new DB_Sql;

        $db->query("INSERT INTO stock_regulation SET
            intranet_id = ".$this->product->kernel->intranet->get('id').",
            product_id = ".$this->product->get('id').",
            product_variation_id = ".$this->product_variation_id.",
            user_id = ".$this->product->kernel->user->get('id').",
            regulation_date_time = NOW(),
            comment = '".$input['description']."',
            quantity = '".$input['quantity']."'");

        return true;
    }

    /**
     * Benyttes til at afstemme lageret med
     *
     * @return boolean true or false
     */
    function adaptation()
    {
        $db = new DB_Sql;

        $db->query("INSERT INTO stock_adaptation SET
            intranet_id = ".$this->product->kernel->intranet->get('id').",
            product_id = ".$this->product->get('id').",
            product_variation_id = ".$this->product_variation_id.",
            user_id = ".$this->product->kernel->user->get('id').",
            adaptation_date_time = NOW(),
            quantity = ".$this->get('actual_stock')."");

        $this->load();

        return true;
    }
}