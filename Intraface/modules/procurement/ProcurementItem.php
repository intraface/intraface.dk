<?php
/**
 * Handles items to procurement
 * 
 * @package Intraface_Procurement
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */

/**
 * Handles items to procurement
 * 
 * @package Intraface_Procurement
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/modules/product/Product.php';

class ProcurementItem extends Intraface_Standard
{
    /**
     * @var integer id of item
     */
    private $id;
    
    /**
     * @var object DB_sql
     */
    private $db;
    
    /**
     * @var object procurement
     */
    private $procurement;
    
    /**
     * @var object product
     */
    private $product;
    
    /**
     * @var object product variation
     */
    private $product_variation;
    
    /**
     * @var obejct product variation detail
     */
    private $product_variation_detail;
    
    /**
     * @var object Ilib_Error
     */
    public $error;

    /**
     * Constructor
     * 
     * @param object Procurement
     * @param integer item id
     * @return void
     */
    public function __construct($procurement, $id)
    {
        if (!is_object($procurement) AND get_class($procurement) != 'Procurement') {
            trigger_error('Procurement: Item kræver procurement', E_USER_ERROR);
        }

        $this->procurement = & $procurement;
        $this->error = new Intraface_Error;
        $this->id = (int) $id;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * load data to object
     * 
     * @return void 
     */
    private function load()
    {
        if ($this->id == 0) {
            return;
        }

        $db = new DB_Sql;

        $db->query("SELECT procurement_item.* FROM procurement_item
                    INNER JOIN procurement ON procurement_item.procurement_id = procurement.id
                    WHERE procurement_item.id = " . $this->id . " AND procurement.id = " . $this->procurement->get('id') . " AND procurement_item.intranet_id = " . $this->procurement->kernel->intranet->get("id"));
        if ($db->nextRecord()) {

            $this->value["id"] = $db->f("id");
            $this->value["product_id"] = $db->f("product_id");
            $this->value["product_detail_id"] = $db->f("product_detail_id");
            $this->value["product_variation_id"] = $db->f("product_variation_id");
            $this->value["product_variation_detail_id"] = $db->f("product_variation_detail_id");
            $this->value["unit_purchase_price"] = $db->f("unit_purchase_price");
            $this->value["dk_unit_purchase_price"] = number_format($db->f("unit_purchase_price"), 2, ",", ".");
            $this->value["quantity"] = $db->f("quantity");
    
        } else {
            $this->id = 0;
            $this->value['id'] = 0;
        }
    }
    
    /**
     * Returns product object with loaded from item
     * 
     * @return object Product
     */
    private function getProduct() 
    {
        if(!$this->product) {
            if($this->get('product_id') == '' || $this->get('product_detail_id') == '') {
                throw new Exception('The item is not loaded');
            }
            $this->product = new Product($this->procurement->kernel, $this->get('product_id'), $this->get('product_detail_id'));
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
            return $this->getProductVariationDetail()->getPrice($this->getProduct());
        }
        else {
            return $this->getProduct()->getDetails()->getPrice();
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
     * Gets the tax percent on the individual product
     *
     * @return float
     */
    public function getProductTaxPercent()
    {
        if ($this->getProduct()->get('vat')) {
            return 25;
        } else {
            return 0;
        }

    }

    /**
     * Saves item
     * 
     * @param array input to be saved
     * @return integer id of item
     */
    function save($input)
    {
        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        settype($input["product_id"], 'integer');
        if ($validator->isNumeric($input["product_id"], "Du skal vælge et produkt", "greater_than_zero")) {
            if (!isset($input['product_detail_id'])) {
                $input['product_detail_id'] = 0;
            }

            require_once 'Intraface/modules/product/Product.php';
            $product = new Product($this->procurement->kernel, $input["product_id"], $input['product_detail_id']);
            
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
        
        $validator->isNumeric($input["quantity"], "Du skal angive et antal", "greater_than_zero,integer");
        
        if(!isset($input["dk_unit_purchase_price"])) $input["dk_unit_purchase_price"] = 0;
        $validator->isDouble($input["dk_unit_purchase_price"], "Du skal angive en indkøbspris", "zero_or_greater");
        $unit_purchase_price = new Intraface_Amount($input["dk_unit_purchase_price"]);
        if ($unit_purchase_price->convert2db()) {
            $input["unit_purchase_price"] = $unit_purchase_price->get();
        } else {
            $this->error->set("Ugyldig indkøbspris");
        }

        if ($this->error->isError()) {
            return (false);
            exit;
        }

        $sql = "product_id = " . $product->getId() . ",
                product_detail_id = " . $product_detail_id . ",
                product_variation_id = " . $variation_id . ",
                product_variation_detail_id = " . $variation_detail_id . ",
                quantity = " . $input["quantity"] . ",
                unit_purchase_price = " . $input["unit_purchase_price"];

        $db = new DB_Sql;

        if ($this->id == 0) {
            $db->query("INSERT INTO procurement_item SET " . $sql . ", intranet_id = " . $this->procurement->kernel->intranet->get("id") . ", procurement_id = " . $this->procurement->get("id") . ", active = 1");
            $this->id = $db->InsertedId();
        } else {
            $db->query("UPDATE procurement_item SET " . $sql . " WHERE id = " . $this->id . " AND procurement_id = " . $this->procurement->get("id") . " AND intranet_id = " . $this->procurement->kernel->intranet->get("id"));
        }

        return $this->id;
    }
    
    /**
     * Sets the purchase price of an item
     * 
     * @param float $price
     * @return boolean true on success
     */
    public function setPurchasePrice($price) {
        
        if ($this->id == 0) {
            throw new Exception('You can only set purchase price when item has been saved');
        }
        $validator = new Intraface_Validator($this->error);
        if($validator->isDouble($price, "Ugyldig indkøbspris", "zero_or_greater")) {
            $unit_purchase_price = new Intraface_Amount($price);
            $unit_purchase_price->convert2db();
        }

        if ($this->error->isError()) {
            return false;
        }

        $sql = "unit_purchase_price = " . $unit_purchase_price->get();

        $db = new DB_Sql;
        $db->query("UPDATE procurement_item SET " . $sql . " WHERE id = " . $this->id . " AND procurement_id = " . $this->procurement->get("id") . " AND intranet_id = " . $this->procurement->kernel->intranet->get("id"));
        return true;
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
        $product = new Product($this->procurement->kernel, $product_id);
        
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
        
        $db = new DB_Sql;
        $db->query("UPDATE procurement_item SET " . $sql . " WHERE id = " . $this->id . " AND procurement_id = " . $this->procurement->get("id") . " AND intranet_id = " . $this->procurement->kernel->intranet->get("id"));

        return true;
    }
    
    /**
     * Deletes item
     * 
     */
    public function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE procurement_item SET active = 0 WHERE intranet_id = " . $this->procurement->kernel->intranet->get('id') . " AND id = " . $this->id . " AND procurement_id = " . $this->procurement->get("id"));
        $this->id = 0;

        return 1;
    }
    
    /**
     * Returns list of items
     * 
     * @return array list of items
     */
    public function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM procurement_item WHERE active = 1 AND intranet_id = " . $this->procurement->kernel->intranet->get("id") . " AND procurement_id = " . $this->procurement->get("id") . " ORDER BY id ASC");
        $i = 0;
        $item = array ();

        if ($this->procurement->get("price_items") > 0) {
            // calculates shipment etc per item price kr
            $calculated = $this->procurement->get("price_shipment_etc") / $this->procurement->get("price_items");
        } else {
            $calculated = 0;
        }

        while ($db->nextRecord()) {
            $product = new Product($this->procurement->kernel, $db->f("product_id"), $db->f("product_detail_id"));
            $item[$i]["id"] = $db->f("id");
            
            $unit = $product->get("unit");
            if ($db->f("quantity") == 1) {
                $item[$i]["unit"] = $unit['singular'];
            } else {
                $item[$i]["unit"] = $unit['plural'];
            }
            $item[$i]["unit_purchase_price"] = $db->f("unit_purchase_price");
            $item[$i]["calculated_unit_price"] = $db->f("unit_purchase_price") + $db->f("unit_purchase_price") * $calculated;
            $item[$i]["quantity"] = $db->f("quantity");
            $item[$i]["vat"] = $product->get("vat");
            $item[$i]["product_id"] = $product->get("id");
            $item[$i]["amount"] = $db->f("quantity") * $db->f("unit_purchase_price");
            
            if($product->get('has_variation')) {
                $variation = $product->getVariation($db->f('product_variation_id'));
                $detail = $variation->getDetail($db->f('product_variation_detail_id'));
                $item[$i]["name"] = $product->get("name").' - '.$variation->getName();
                $item[$i]["number"]= $product->get("number").'.'.$variation->getNumber();
                $item[$i]["price"] = $detail->getPrice($product);
                
            }
            else {
                $item[$i]["name"] = $product->get("name");
                $item[$i]["number"] = $product->get("number");
                $item[$i]["price"] = $product->getDetails()->getPrice();
            }
            $i++;

        }
        return $item;
    }

    /**
     * Returns the quantity of a given product used
     */
    public function getQuantity($status, $product_id, $product_variation_id, $from_date = "")
    {
        if (!in_array($status, array (
                'ordered',
                'delivered'
            ))) {
            trigger_error("Ugyldig status", FATAL);
        }

        $db = new DB_sql;

        if ($status == "ordered") {
            $db->query("SELECT SUM(quantity) AS on_order
                            FROM procurement_item INNER JOIN procurement
                                ON procurement_item.procurement_id = procurement.id
                            WHERE procurement_item.active = 1 AND procurement.active = 1
                                AND procurement_item.intranet_id = " . $this->procurement->kernel->intranet->get("id") . " AND procurement.intranet_id = " . $this->procurement->kernel->intranet->get("id") . "
                                AND procurement_item.product_id = " . $product_id . " AND procurement.status_key = 0
                                AND procurement_item.product_variation_id = ".$product_variation_id);
            $db->nextRecord(); // Der vil altid være en post
            return intval($db->f("on_order"));
        } else {
            // delivered

            $db->query("SELECT SUM(quantity) AS stock_in
                        FROM procurement_item INNER JOIN procurement
                            ON procurement_item.procurement_id = procurement.id
                        WHERE procurement_item.active = 1 AND procurement.active = 1
                            AND procurement_item.intranet_id = " . $this->procurement->kernel->intranet->get("id") . " AND procurement.intranet_id = " . $this->procurement->kernel->intranet->get("id") . "
                            AND procurement_item.product_id = " . $product_id . "
                            AND procurement_item.product_variation_id = ".$product_variation_id."
                            AND procurement.status_key = 1
                            AND procurement.date_recieved > \"" . $from_date . "\"");
            $db->nextRecord(); // Der vil altid være en post
            return intval($db->f("stock_in"));
        }
    }
}