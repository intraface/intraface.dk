<?php
/**
 * Handles items to debtor (quotation, order, invoice, credit note)
 *
 * @package Intraface_Debtor
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
 
/**
 * Handles items to debtor (quotation, order, invoice, credit note)
 *
 * @package Intraface_Debtor
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
class DebtorItem extends Intraface_Standard
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
    private $product;

    /**
     * @var object Intraface_modules_product_Variation
     */
    private $product_variation;
    
    /**
     * @var object Intraface_modules_product_Variation_Detail
     */
    private $product_variation_detail;
    
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
            trigger_error('Debtor: Item kræver debtor', E_USER_ERROR);
        }

        $this->debtor = $debtor;
        $this->db = new DB_Sql;
        $this->error = new Intraface_Error;
        $this->id = (int)$id;

        if ($this->id > 0) {
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
        if ($this->id == 0) {
            return;
        }
        $this->db->query("SELECT id, product_id, product_detail_id, product_variation_id, product_variation_detail_id, description, quantity FROM debtor_item WHERE id = ".$this->id." AND intranet_id = ".$this->debtor->kernel->intranet->get("id"));
        if ($this->db->nextRecord()) {
            $this->value["id"] = $this->db->f("id");
            $this->value["product_id"] = $this->db->f("product_id");
            $this->value["product_detail_id"] = $this->db->f("product_detail_id");
            $this->value["product_variation_id"] = $this->db->f("product_variation_id");
            $this->value["product_variation_detail_id"] = $this->db->f("product_variation_detail_id");
            $this->value["description"] = $this->db->f("description");
            $this->value["quantity"] = $this->db->f("quantity");
        }
    }
    
    /**
     * returns product object with loaded from item
     * 
     * @return object Product
     */
    private function getProduct() 
    {
        if(!$this->product) {
            if($this->get('product_id') == '' || $this->get('product_detail_id') == '') {
                throw new Exception('The item is not loaded');
            }
            $this->product = new Product($this->debtor->kernel, $this->get('product_id'), $this->get('product_detail_id'));
        }
        return $this->product;
    }
    
    /**
     * Returns Product variation loaded from item
     * 
     * @return object Intraface_modules_product_Variation
     */
    private function getProductVariation()
    {
        if(!$this->getProduct()->get('has_variation')) {
            throw new Exception('The product must have variation to request variations');    
        }
        if(!$this->product_variation) {
            if(intval($this->get('product_variation_id')) == 0) {
                throw new Exception('The product variation id is not valid on item '.$this->get('id'));
            }
            $this->product_variation = $this->product->getVariation($this->get('product_variation_id'));
        }
        return $this->product_variation;
    }
    
    /**
     * Returns product variation detail loaded from item
     * 
     * @return object Intraface_modules_product_Variation_Detail
     */
    private function getProductVariationDetail()
    {
        if(!$this->getProduct()->get('has_variation')) {
            throw new Exception('The product must have variation to request variations');    
        }
        if(!$this->product_variation_detail) {
            if(intval($this->get('product_variation_detail_id')) == 0) {
                throw new Exception('The product variation detail id is not valid on item '.$this->get('id'));
            }
            $this->product_variation_detail = $this->getProductVariation()->getDetail($this->get('product_variation_detail_id'));
        }
        return $this->product_variation_detail;
    }
       
    /**
     * Returns price of product without vat
     * 
     * @return float price of product
     */
    public function getProductPrice()
    {
        if($this->getProduct()->get('has_variation')) {
            return $this->getProduct()->get("price") + $this->getProductVariationDetail()->getPriceDifference();
        }
        else {
            return $this->getProduct()->get("price");
        }
    }
    
    /**
     * Returns weight of product without vat
     * 
     * @return integer weight of product
     */
    public function getProductWeight()
    {
        if($this->getProduct()->get('has_variation')) {
            return $this->getProduct()->get("weight") + $this->getProductVariationDetail()->getWeightDifference();
        }
        else {
            return $this->getProduct()->get("weight");
        }
    }
    
    /**
     * Returns number of product
     * 
     * @return string product number
     */
    public function getProductNumber() 
    {
        if($this->getProduct()->get('has_variation')) {
            return $this->getProduct()->get("number").'.'.$this->getProductVariation()->getNumber();
        }
        else {
            return $this->getProduct()->get("number");
        }
    }
    
    /**
     * Returns name of product
     * 
     * @return string name of product
     */
    public function getProductName()
    {
        if($this->getProduct()->get('has_variation')) {
            return $this->getProduct()->get("name").' - '.$this->getProductVariation()->getName();
        }
        else {
            return $this->getProduct()->get("name");
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
        return $this->getProductPrice() * $this->get('quantity');
    }

    /**
     * Gets weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getProductWeight() * $this->get('quantity');
    }

    /**
     * Gets the tax percent on the individual product
     *
     * @return float
     */
    public function getTaxPercent()
    {
        if ($this->getProduct()->get('vat')) {
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
        if ($this->debtor->get("locked") == 1) {
            $this->error->set('Posten er låst er låst og der kan ikke opdateres varer på den');
            return 0;
        }

        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        settype($input["product_id"], 'integer');
        if ($validator->isNumeric($input["product_id"], "Du skal vælge et produkt", "greater_than_zero")) {
            if (!isset($input['product_detail_id'])) {
                $input['product_detail_id'] = 0;
            }

            require_once 'Intraface/modules/product/Product.php';
            $product = new Product($this->debtor->kernel, $input["product_id"], $input['product_detail_id']);
            
            if (!is_object($product) || $product->get('id') == 0) {
                 $this->error->set("Ugyldigt produkt");
            } else {
                $product_detail_id = $product->get("detail_id");
            }
            
            if(!isset($input['product_variation_id'])) $input['product_variation_id'] = 0;
            if(intval($input['product_variation_id']) != 0) {
                $variation = $product->getVariation(intval($input['product_variation_id']));
                if(!$variation->getId()) {
                    $this->error->set("Invalid product variation");
                }
                
                if(!isset($input['product_variation_detail_id'])) $input['product_variation_detail_id'] = 0;
                $detail = $variation->getDetail(intval($input['product_variation_detail_id']));
                if(!$detail->getId()) {
                    $this->error->set("Invalid product variation detail");
                }
                
                $variation_id = $variation->getId();
                $variation_detail_id = $detail->getId();
            }
            else {
                $variation_id = 0;
                $variation_detail_id = 0;
            }
            
        }

        if (!isset($input["quantity"])) $input["quantity"] = 0;
        $validator->isDouble($input["quantity"], "Du skal angive et antal", "");
        $quantity = new Intraface_Amount($input["quantity"]);
        if ($quantity->convert2db()) {
            $input["quantity"] = $quantity->get();
        } else {
            $this->error->set("Ugyligt antal");
        }
        if (!isset($input['description'])) $input['description'] = '';
        $validator->isString($input["description"], "Fejl i beskrivelse", "<b><i>", "allow_empty");

        if ($this->error->isError()) {
            return(false);
        }

        $sql = "product_id = ".$input["product_id"].",
            product_detail_id = ".$product_detail_id.",
            product_variation_id = ".$variation_id.",
            product_variation_detail_id = ".$variation_detail_id.",
            quantity = ".$input["quantity"].",
            description = '".$input["description"]."'";

        if ($this->id == 0) {
            $position = $this->getPosition(MDB2::singleton(DB_DSN))->getMaxPosition() + 1;
            $sql = $sql.', position = '.$position;

            $this->db->query("INSERT INTO debtor_item SET ".$sql.", intranet_id = ".$this->debtor->kernel->intranet->get("id").", debtor_id = ".$this->debtor->get("id").", active = 1");
            $this->id = $this->db->InsertedId();
        } else {
            $this->db->query("UPDATE debtor_item SET ".$sql." WHERE id = ".$this->id." and debtor_id = ".$this->debtor->get("id"));
        }

        // hvis det er et kreditnota, skal fakturastatus opdateres
        if ($this->debtor->get("type") == "credit_note" && $this->debtor->get("where_from") == "invoice" && $this->debtor->get("where_from_id") != 0) {
            $invoice = Debtor::factory($this->debtor->kernel, (int)$this->debtor->get("where_from_id"));
            $invoice->updateStatus();
        }

        return $this->id;
    }
    
    /**
     * Changes the product assigned to the item
     * 
     * @param integer $product_id
     * @param integer $product_variation_id
     * @return boolean true on success
     */
    public function changeProduct($product_id, $product_variation_id = 0)
    {
        if(!$this->id) {
            throw new Exception('You cannot change product when not saved');
        }
        
        require_once 'Intraface/modules/product/Product.php';
        $product = new Product($this->debtor->kernel, $product_id);
        
        if (!is_object($product) || $product->get('id') == 0) {
             throw new Excetion('Invalid product id');
        } else {
            $product_detail_id = $product->get("detail_id");
        }
        
        if(intval($product_variation_id) != 0) {
            $variation = $product->getVariation(intval($product_variation_id));
            if(!$variation->getId()) {
                throw new Exception('Invalid product variation id');
            }
            
            $detail = $variation->getDetail();
            if(!$detail->getId()) {
                throw new Exception("Invalid product variation detail");
            }
            
            $variation_id = $variation->getId();
            $variation_detail_id = $detail->getId();
        }
        else {
            $variation_id = 0;
            $variation_detail_id = 0;
        }
        
        $sql = "product_id = ".$product->getId().",
            product_detail_id = ".$product_detail_id.",
            product_variation_id = ".$variation_id.",
            product_variation_detail_id = ".$variation_detail_id;
        
        $this->db->query("UPDATE debtor_item SET ".$sql." WHERE id = ".$this->id." and debtor_id = ".$this->debtor->get("id"));
        return true;
    }

    /**
     * Deletes a product
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->debtor->get("locked") == true) {
            $this->error->set('Du kan ikke slette vare til en låst post');
            return false;
        }
        $this->db->query("UPDATE debtor_item SET active = 0 WHERE id = ".$this->id." AND debtor_id = ".$this->debtor->get("id"));
        $this->id = 0;

        if ($this->debtor->get("type") == "credit_note" && $this->debtor->get("where_from") == "invoice" && $this->debtor->get("where_from_id") != 0) {
            $invoice = Debtor::factory($this->debtor->kernel, intval($this->debtor->get("where_from_id")));
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

        $db->query("SELECT id, product_id, product_detail_id, product_variation_id, product_variation_detail_id, quantity, description FROM debtor_item WHERE active = 1 AND intranet_id = ".$this->debtor->kernel->intranet->get("id")." AND debtor_id = ".$this->debtor->get("id")." ORDER BY position ASC, id ASC");
        $i = 0;
        $j = 0;
        $item_no_vat = array();
        $item_with_vat = array();

        require_once 'Intraface/modules/product/Product.php';
        
        while($db->nextRecord()) {
            $product = new Product($this->debtor->kernel, $db->f('product_id'), $db->f('product_detail_id'));
            
            if ($product->getId() != 0 && $product->get('detail_id') != 0) {
                
                $item = array();
                $item["description"] = $db->f("description");
                $item["quantity"] = $db->f("quantity");
                $item["id"] = $db->f("id");
                
                $item["vat"] = $product->get("vat");
                $item["product_id"] = $product->getId();
                $item["product_detail_id"] = $product->get("detail_id");
                $unit = $product->get('unit');
                if ($db->f("quantity") == 1) {
                    $item["unit"] = $unit['singular'];
                } else {
                    $item["unit"] = $unit['plural'];
                }
                
                if($product->get('has_variation')) {
                    $variation = $product->getVariation($db->f('product_variation_id'));
                    $detail = $variation->getDetail($db->f('product_variation_detail_id'));
                    $item["name"] = $product->get("name").' - '.$variation->getName();
                    $item["number"]= $product->get("number").'.'.$variation->getNumber();
                    $item["price"] = $product->get("price") + $detail->getPriceDifference();
                }
                else {
                    $item["name"] = $product->get("name");
                    $item["number"]= $product->get("number");
                    $item["price"] = $product->get("price");
                }
                    
                if ($product->get("vat") == 0) {
                    $item_no_vat[$j] = $item;
                    $item_no_vat[$j]["amount"] = $item["quantity"] * $item["price"];
                    $j++;
                } else {
                    $item_with_vat[$i] = $item;
                    $item_with_vat[$i]["amount"] = $item["quantity"] * $item["price"] * 1.25;
                    $i++;
                }
            } else {
                 trigger_error("Ugyldig produktdetalje i DebtorItem->getList() on ".$db->f('product_id').'/'.$db->f('product_detail_id'), E_USER_ERROR);
            }
        }
        return(array_merge($item_with_vat, $item_no_vat));
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
    public function getQuantity($product_id, $product_variation_id, $from_date, $sent = "")
    {

        /*
        0=>'created',
            1=>'sent',
            2=>'executed',
            3=>'cancelled'
        */

        if (!in_array($sent, array("", "not_sent"))) {
            trigger_error("Ugyldig værdi i 3. parameter til debtor->item->getQuantity()", E_USER_ERROR);
        }

        if ($this->debtor->get('type') == "quotation") {
            $status_sql = "debtor.status = 0 OR debtor.status = 1"; // tilbud der er oprettet eller sent.
            $date_sql = "";
        } elseif ($this->debtor->get('type') == "order") {
            $status_sql = "debtor.status = 0 OR debtor.status = 1"; // ordre der er oprettet eller sent.
            $date_sql = "";
        } elseif ($this->debtor->get('type') == "invoice" && $sent == "") {
            $status_sql = "debtor.status = 1 OR debtor.status = 2"; // fakturaer der er sent eller færdigbehandlet
            $date_sql = "AND debtor.date_sent > \"".$from_date."\"";
        } elseif ($this->debtor->get('type') == "invoice" && $sent == "not_sent") {
            $status_sql = "debtor.status = 0"; // fakturaer der er oprettet.
            $date_sql = "";
        } elseif ($this->debtor->get('type') == "credit_note") {
            $status_sql = "debtor.status = 2"; // kredit notaer der er færdigbehandlet
            $date_sql = "AND debtor.date_executed > \"".$from_date."\"";
        } else {
            trigger_error("Der er opstået en fejl i Debtor->item->getQuantity()", E_USER_ERROR);
        }

        $db = new DB_sql;

        $sql = "SELECT SUM(quantity) AS sum_quantity
            FROM debtor_item INNER JOIN debtor
                ON debtor_item.debtor_id = debtor.id
            WHERE debtor_item.active = 1 AND debtor.active = 1
                AND debtor_item.intranet_id = ".$this->debtor->kernel->intranet->get("id")." AND debtor.intranet_id = ".$this->debtor->kernel->intranet->get("id")."
                AND debtor.type = ".$this->debtor->get('type_key')." AND (".$status_sql.")
                AND debtor_item.product_id = ".intval($product_id)."
                AND debtor_item.product_variation_id = ".intval($product_variation_id)." ".$date_sql;
        // print("\n".$this->debtor->get('type').":".$sql."\n");
        $db->query($sql);
        $db->nextRecord(); // Der vil altid være en post
        return intval($db->f("sum_quantity"));
    }

    /**
     * Returns position object
     * 
     * @return object Ilib_Position
     */
    function getPosition($db)
    {
        return new Ilib_Position($db, "debtor_item", $this->id, "intranet_id=".$this->debtor->kernel->intranet->get('id')." AND debtor_id=".$this->debtor->get('id')." AND active = 1", "position", "id");
    }
}