<?php
/**
 * @package Intraface_Procurement
 * TODO Der bør være mulighed for halve antal?
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */


class ProcurementItem extends Standard {
    var $value;
    var $id;
    var $db;
    var $procurement;
    var $product;
    var $product_id;
    var $error;

    function ProcurementItem($procurement, $id) {
        if (!is_object($procurement) AND get_class($procurement) != 'Procurement') {
        trigger_error('Procurement: Item kræver procurement', FATAL);
    }

        $this->procurement = &$procurement;
        $this->error = new Error;
        $this->id = (int)$id;



        if($this->id > 0) {
            $this->load();
        }
    }

    function load() {



        if($this->id == 0) {
     return;
    }

        $db = new DB_Sql;

        $db->query("SELECT procurement_item.* FROM procurement_item
            INNER JOIN procurement ON procurement_item.procurement_id = procurement.id
            WHERE procurement_item.id = ".$this->id." AND procurement.id = ".$this->procurement->get('id')." AND procurement_item.intranet_id = ".$this->procurement->kernel->intranet->get("id"));
        if($db->nextRecord()) {

            $this->value["id"] = $db->f("id");
            $this->value["product_id"] = $db->f("product_id");
            $this->value["product_detail_id"] = $db->f("product_detail_id");
            $this->value["unit_purchase_price"] = $db->f("unit_purchase_price");
            $this->value["dk_unit_purchase_price"] = number_format($db->f("unit_purchase_price"), 2, ",", ".");
            $this->value["quantity"] = $db->f("quantity");

            // Her loader vi gammel produktdetaljer
            $this->product = new Product($this->procurement->kernel, $this->value["product_id"], $this->value["product_detail_id"]);
        }
        else {
            $this->id = 0;
        }
    }

    function update($input) {
        /*
        Ingen lock funktion i Procurement pt.
        if($this->procurement->locked == 1) {
            $this->error->set('Posten er låst er låst og der kan ikke opdateres varer på den');
            return 0;
    }
        */

        $input = safeToDb($input);

        $validator = new Validator($this->error);

        if($validator->isNumeric($input["product_id"], "Du skal vælge et produkt", "greater_than_zero")) {
            $product = new Product($this->procurement->kernel, $input["product_id"]);

             if(!is_object($product) && $product->get('id') == 0) {
                 $this->error->set("Ugyldigt produkt");
             }
            else {
                $product_detail_id = $product->get("detail_id");
            }
         }

        $validator->isNumeric($input["quantity"], "Du skal angive et antal", "greater_than_zero,integer");

        $validator->isDouble($input["dk_unit_purchase_price"], "Du skal angive en indkøbspris", "zero_or_greater");
        $unit_purchase_price = new Amount($input["dk_unit_purchase_price"]);
        if($unit_purchase_price->convert2db()) {
            $input["unit_purchase_price"] = $unit_purchase_price->get();
        }
        else {
            $this->error->set("Ugyldig indkøbspris");
        }

         if($this->error->isError()) {
            return(false);
            exit;
        }

       $sql = "product_id = ".$input["product_id"].",
            product_detail_id = ".$product_detail_id.",
        quantity = ".$input["quantity"].",
        unit_purchase_price = ".$input["unit_purchase_price"];

        $db = new Db_sql;

       if($this->id == 0) {
           $db->query("INSERT INTO procurement_item SET ".$sql.", intranet_id = ".$this->procurement->kernel->intranet->get("id").", procurement_id = ".$this->procurement->get("id").", active = 1");
            $this->id = $db->InsertedId();
       }
       else {
           $db->query("UPDATE procurement_item SET ".$sql." WHERE id = ".$this->id." AND procurement_id = ".$this->procurement->get("id")." AND intranet_id = ".$this->procurement->kernel->intranet->get("id"));
       }

        return $this->id;
    }

    function delete() {

        $db = new Db_sql;
        $db->query("UPDATE procurement_item SET active = 0 WHERE intranet_id = ".$this->procurement->kernel->intranet->get('id')." AND id = ".$this->id." AND procurement_id = ".$this->procurement->get("id"));
        $this->id = 0;

    return 1;
    }

    function getList() {

        $db = new DB_sql;
        $db->query("SELECT * FROM procurement_item WHERE active = 1 AND intranet_id = ".$this->procurement->kernel->intranet->get("id")." AND procurement_id = ".$this->procurement->get("id")." ORDER BY id ASC");
        $i = 0;
        $item = array();

        if($this->procurement->get("total_price_items") > 0) {
            $calculated = ($this->procurement->get("total_price") - $this->procurement->get("total_price_items") - $this->procurement->get("vat")) / $this->procurement->get("total_price_items");
        }
        else {
            $calculated = 0;
        }

        while($db->nextRecord()) {
            $product = new Product($this->procurement->kernel, $db->f("product_id"), $db->f("product_detail_id"));
            $item[$i]["id"] = $db->f("id");
            $item[$i]["name"] = $product->get("name");
            $item[$i]["number"]= $product->get("number");
            $item[$i]["unit"] = $product->get("unit");
            $item[$i]["unit_purchase_price"] = $db->f("unit_purchase_price");
            $item[$i]["calculated_unit_price"] = $db->f("unit_purchase_price") + $db->f("unit_purchase_price") * $calculated;
            $item[$i]["quantity"] = $db->f("quantity");
            // $item[$i]["description"] = $db->f("description");
            $item[$i]["vat"] = $product->get("vat");
            $item[$i]["product_id"] = $product->get("id");
            $item[$i]["amount"] = $db->f("quantity") * $product->get("unit_purchase_price") * 1.25;

            $i++;

        }
        return($item);
    }

    function getQuantity($status, $product_id, $from_date = "") {
        if(!in_array($status, array('ordered', 'delivered'))) {
            trigger_error("Ugyldig status", FATAL);
        }

        $db = new DB_sql;

        if($status == "ordered") {
            $db->query("SELECT SUM(quantity) AS on_order
                FROM procurement_item INNER JOIN procurement
                    ON procurement_item.procurement_id = procurement.id
                WHERE procurement_item.active = 1 AND procurement.active = 1
                    AND procurement_item.intranet_id = ".$this->procurement->kernel->intranet->get("id")." AND procurement.intranet_id = ".$this->procurement->kernel->intranet->get("id")."
                    AND procurement_item.product_id = ".$product_id." AND procurement.status_key = 0");
            $db->nextRecord(); // Der vil altid være en post
            return intval($db->f("on_order"));
        }
        else {
            // delivered

            $db->query("SELECT SUM(quantity) AS stock_in
            FROM procurement_item INNER JOIN procurement
                ON procurement_item.procurement_id = procurement.id
            WHERE procurement_item.active = 1 AND procurement.active = 1
                AND procurement_item.intranet_id = ".$this->procurement->kernel->intranet->get("id")." AND procurement.intranet_id = ".$this->procurement->kernel->intranet->get("id")."
                AND procurement_item.product_id = ".$product_id." AND procurement.status_key = 1
                AND procurement.date_recieved > \"".$from_date."\"");
            $db->nextRecord(); // Der vil altid være en post
            return intval($db->f("stock_in"));
        }
    }

}
?>