<?php
/**
 * Der b�r v�re mulighed for halve antal?
 *
 * @package Intraface_Debtor
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/Standard.php';

class DebtorItem extends Standard
{
    /**
     * @var array
     */
    public $value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var object
     */
    private $invoice;

    /**
     * @var object
     */
    private $db;

    /**
     * @var object
     */
    public $product;

    /**
     * @var integer
     */
    private $product_id;

    /**
     * @var object
     */
    public $error;

    /**
     * Constructor
     *
     * @param object  $debtor Debtor object
     * @param integer $id     If a special item id is needed
     *
     * @return void
     */
    public function __construct($debtor, $id = 0)
    {
        if (!is_object($debtor)) {
            trigger_error('Debtor: Item kr�ver debtor', E_USER_ERROR);
        }

        $this->debtor = &$debtor;
        $this->db = new Db_sql;
        require_once 'Intraface/Error.php';
        $this->error = new Error;
        $this->id = (int)$id;

        if($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Loads data
     *
     * @return void
     */
    private function load()
    {
        if($this->id == 0) {
            return;
        }
        // TODO LIMIT 1 er s�dan set noget m�rkeligt noget. Der skulle gerne kun v�re 1 da man s�ger p� id, og hvis der endelig er mere end 1,
        // burde man istedet udskrive en fejlmeddelse, for det m� ikke kunne ske /Sune (15/3 2005)
        $this->db->query("SELECT product_id, id, description, quantity FROM debtor_item WHERE id = ".$this->id." AND intranet_id = ".$this->debtor->kernel->intranet->get("id")." LIMIT 1");
        if($this->db->nextRecord()) {
            $this->product_id = $this->db->f("product_id");

            $this->value["id"] = $this->db->f("id");
            $this->value["product_id"] = $this->db->f("product_id");
            $this->value["description"] = $this->db->f("description");
            $this->value["quantity"] = $this->db->f("quantity");

            // TODO
            // hvad g�re f�lgende godt for?
            // Det var lavet fordi n�r man skulle rette et item kunne man n�jes med at sende item_id med til item_edit.php istedet
            // b�de at sendet item_id og f.eks. invoice_id. S� fandt den selv invoice ud fra item, men nu har du lavet det s�dan at
            // at man sender invoice_id med, og s� har den ikke nogen effekt mere. Jeg mener faktisk at det var den rigtige m�de at
            // g�re det p� f�r, da man sagtens kan n�jes med at sende et id, og nu giver kun mulighed for fejl at sende begge, og
            // s�rger for at de rent faktisk tilh�rer sammen faktura /Sune (15/3 2005)

            // if($this->debtor->get("id") != $this->db->f("debtor_id")) {
            //	 $this->debtor->id = $this->db->f("debtor_id");
            // 	 $this->debtor->load();
            // }

            // Her loader vi ikke gammel produktdetaljer da vi skal kunn - hvorfor g�r vi ikke det?
            $this->product = new Product($this->debtor->kernel, $this->product_id);
        }
    }

    /**
     * Gets price without taxes
     *
     * @return float
     */
    public function getPrice()
    {
        // TODO how do we handle vat? this should return raw prices
        // and then the tax percent should return the tax to apply
        // the calculator should handle the final price
        return $this->product->get('price') * $this->get('quantity');
    }

    /**
     * Gets weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->product->get('weight') * $this->get('quantity');
    }

    /**
     * Gets the tax percent on the individual product
     *
     * @return float
     */
    public function getTaxPercent()
    {
        if ($this->product->get('vat')) {
            return 25;
        } else {
            return 0;
        }

    }

    /**
     * Saves data
     *
     * @TODO: Should have product object instead of product id in the param array.
     *
     * @param array $input Values to save
     *
     * @return integer
     */
    public function save($input)
    {
        if($this->debtor->get("locked") == 1) {
            $this->error->set('Posten er l�st er l�st og der kan ikke opdateres varer p� den');
            return 0;
        }

        $input = safeToDb($input);

        require_once 'Intraface/Validator.php';
        $validator = new Validator($this->error);

        settype($input["product_id"], 'integer');
        if($validator->isNumeric($input["product_id"], "Du skal v�lge et produkt", "greater_than_zero")) {
            if(!isset($input['product_detail_id'])) {
                $input['product_detail_id'] = 0;
            }

            require_once 'Intraface/modules/product/Product.php';
            $product = new Product($this->debtor->kernel, $input["product_id"], $input['product_detail_id']);

            if(!is_object($product) || $product->get('id') == 0) {
                 $this->error->set("Ugyldigt produkt");
            } else {
                $product_detail_id = $product->get("detail_id");
            }
        }

        if(!isset($input["quantity"])) $input["quantity"] = 0;
        $validator->isDouble($input["quantity"], "Du skal angive et antal", "");
        require_once 'Intraface/tools/Amount.php';
        $quantity = new Amount($input["quantity"]);
        if($quantity->convert2db()) {
            $input["quantity"] = $quantity->get();
        } else {
            $this->error->set("Ugyligt antal");
        }
        if(!isset($input['description'])) $input['description'] = '';
        $validator->isString($input["description"], "Fejl i beskrivelse", "<b><i>", "allow_empty");

        if($this->error->isError()) {
            return(false);
        }

        $sql = "product_id = ".$input["product_id"].",
            product_detail_id = ".$product_detail_id.",
            quantity = ".$input["quantity"].",
            description = '".$input["description"]."'";

        if($this->id == 0) {
            $position = $this->getPosition(MDB2::singleton(DB_DSN))->maxPosition() + 1;
            $sql = $sql.', position = '.$position;

            $this->db->query("INSERT INTO debtor_item SET ".$sql.", intranet_id = ".$this->debtor->kernel->intranet->get("id").", debtor_id = ".$this->debtor->get("id").", active = 1");
            $this->id = $this->db->InsertedId();
        } else {
            $this->db->query("UPDATE debtor_item SET ".$sql." WHERE id = ".$this->id." and debtor_id = ".$this->debtor->get("id"));
        }

        // hvis det er et kreditnota, skal fakturastatus opdateres
        if($this->debtor->get("type") == "credit_note" && $this->debtor->get("where_from") == "invoice" && $this->debtor->get("where_from_id") != 0) {
            $invoice = Debtor::factory($this->debtor->kernel, $this->debtor->get("where_from_id"));
            $invoice->updateStatus();
        }

        return $this->id;
    }

    /**
     * Deletes a product
     *
     * @return boolean
     */
    public function delete()
    {
        if($this->debtor->get("locked") == true) {
            $this->error->set('Du kan ikke slette vare til en l�st post');
            return false;
        }
        $this->db->query("UPDATE debtor_item SET active = 0 WHERE id = ".$this->id." AND debtor_id = ".$this->debtor->get("id"));
        $this->id = 0;

        if($this->debtor->get("type") == "credit_note" && $this->debtor->get("where_from") == "invoice" && $this->debtor->get("where_from_id") != 0) {
            $invoice = Debtor::factory($this->debtor->kernel, $this->debtor->get("where_from_id"));
            $invoice->updateStatus();
        }

        return true;
    }

    /**
     * Get a list with Debtor items
     *
     * @return array
     */
    public function getList()
    {
        $db = new DB_sql;
        $db2 = new DB_sql;

        $db->query("SELECT id, product_id, product_detail_id, quantity, description FROM debtor_item WHERE active = 1 AND intranet_id = ".$this->debtor->kernel->intranet->get("id")." AND debtor_id = ".$this->debtor->get("id")." ORDER BY position ASC, id ASC");
        $i = 0;
        $j = 0;
        $item_no_vat = array();
        $item = array();

        require_once 'Intraface/modules/product/Product.php';
        $units = Product::getUnits();

        while($db->nextRecord()) {
            if ($db->f('product_detail_id') == 0) {
                continue;
            }
            // Todo: This is not really good. Either we join or we get it from Product class
            $sql = "SELECT name, number, unit, price, vat, product_id FROM product_detail
                    WHERE intranet_id = ".$this->debtor->kernel->intranet->get('id')."
                        AND product_id = ".$db->f('product_id')."
                        AND id = ".$db->f('product_detail_id');
            $db2->query($sql);

            if ($db2->nextRecord()) {
                if($db2->f("vat") == 0) {
                    $item_no_vat[$j]["id"] = $db->f("id");
                    $item_no_vat[$j]["name"] = $db2->f("name");
                    $item_no_vat[$j]["number"]= $db2->f("number");
                    if($db->f("quantity") == 1) {
                        $item_no_vat[$j]["unit"] = $units[$db2->f("unit")]['singular'];
                    } else {
                        $item_no_vat[$j]["unit"] = $units[$db2->f("unit")]['plural'];
                    }
                    $item_no_vat[$j]["price"] = $db2->f("price");
                    $item_no_vat[$j]["quantity"] = $db->f("quantity");
                    $item_no_vat[$j]["description"] = $db->f("description");
                    $item_no_vat[$j]["vat"] = $db2->f("vat");
                    $item_no_vat[$j]["product_id"] = $db->f("product_id");
                    $item_no_vat[$j]["product_detail_id"] = $db->f("product_detail_id");
                    $item_no_vat[$j]["amount"] = $db->f("quantity") * $db2->f("price");
                    $j++;
                } else {
                    $item[$i]["id"] = $db->f("id");
                    $item[$i]["name"] = $db2->f("name");
                    $item[$i]["number"]= $db2->f("number");
                    if($db->f("quantity") == 1) {
                        $item[$i]["unit"] = $units[$db2->f("unit")]['singular'];
                    } else {
                        $item[$i]["unit"] = $units[$db2->f("unit")]['plural'];
                    }
                    $item[$i]["price"] = $db2->f("price");
                    $item[$i]["quantity"] = $db->f("quantity");
                    $item[$i]["description"] = $db->f("description");
                    $item[$i]["vat"] = $db2->f("vat");
                    $item[$i]["product_id"] = $db->f("product_id");
                    $item[$i]["product_detail_id"] = $db->f("product_detail_id");
                    $item[$i]["amount"] = $db->f("quantity") * $db2->f("price") * 1.25;

                    $i++;
                }
            } else {
                 trigger_error("Ugyldig produktdetalje i DebtorItem->getList() on " . $sql, E_USER_ERROR);
            }
        }
        return(array_merge($item, $item_no_vat));
    }

    /**
     * Method to get quantity of each product
     *
     * TODO This should not be in this class
     *
     * @param integer $product_id Product id
     * @param string  $from_date  Which date to start the quantity from
     * @param string  $sent       TODO WHAT IS THIS
     *
     * @return integer
     */
    public function getQuantity($product_id, $from_date, $sent = "")
    {

        /*
        0=>'created',
            1=>'sent',
            2=>'executed',
            3=>'cancelled'
        */

        if(!in_array($sent, array("", "not_sent"))) {
            trigger_error("Ugyldig v�rdi i 3. parameter til debtor->item->getQuantity()", E_USER_ERROR);
        }

        if($this->debtor->get('type') == "quotation") {
            $status_sql = "debtor.status = 0 OR debtor.status = 1"; // tilbud der er oprettet eller sent.
            $date_sql = "";
        } elseif($this->debtor->get('type') == "order") {
            $status_sql = "debtor.status = 0 OR debtor.status = 1"; // ordre der er oprettet eller sent.
            $date_sql = "";
        } elseif($this->debtor->get('type') == "invoice" && $sent == "") {
            $status_sql = "debtor.status = 1 OR debtor.status = 2"; // fakturaer der er sent eller f�rdigbehandlet
            $date_sql = "AND debtor.date_sent > \"".$from_date."\"";
        } elseif($this->debtor->get('type') == "invoice" && $sent == "not_sent") {
            $status_sql = "debtor.status = 0"; // fakturaer der er oprettet.
            $date_sql = "";
        } elseif($this->debtor->get('type') == "credit_note") {
            $status_sql = "debtor.status = 2"; // kredit notaer der er f�rdigbehandlet
            $date_sql = "AND debtor.date_executed > \"".$from_date."\"";
        } else {
            trigger_error("Der er opst�et en fejl i Debtor->item->getQuantity()", E_USER_ERROR);
        }

        $db = new DB_sql;

        $sql = "SELECT SUM(quantity) AS sum_quantity
            FROM debtor_item INNER JOIN debtor
                ON debtor_item.debtor_id = debtor.id
            WHERE debtor_item.active = 1 AND debtor.active = 1
                AND debtor_item.intranet_id = ".$this->debtor->kernel->intranet->get("id")." AND debtor.intranet_id = ".$this->debtor->kernel->intranet->get("id")."
                AND debtor.type = ".$this->debtor->get('type_key')." AND (".$status_sql.")
                AND debtor_item.product_id = ".intval($product_id)." ".$date_sql;
        // print("\n".$this->debtor->get('type').":".$sql."\n");
        $db->query($sql);
        $db->nextRecord(); // Der vil altid v�re en post
        return intval($db->f("sum_quantity"));
    }

    function getPosition($db)
    {
        require_once 'Ilib/Position.php';
        return new Ilib_Position($db, "debtor_item", $this->id, "intranet_id=".$this->debtor->kernel->intranet->get('id')." AND debtor_id=".$this->debtor->get('id')." AND active = 1", "position", "id");
    }

    /**
     * Moves debtor item one up
     *
     * @return void
     */
    public function moveUp()
    {
        $this->getPosition()->moveUp($this->id);
    }
}