<?php
/**
 * Product
 *
 * Bruges til at holde styr på varerne.
 *
 * @package Intraface_Product
 * @author Lars Olesen <lars@legestue.net>
 * @see ProductDetail
 * @see Stock
 */
require_once 'Intraface/modules/product/ProductDetail.php';

class Product extends Intraface_Standard
{
    /**
     * @var object
     */
    public $kernel;

    /**
     * @var object
     */
    public $user;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var object
     */
    private $detail;

    /**
     * @var integer
     */
    private $old_product_detail_id;

    /**
     * @var array
     */
    public $value = array();

    /**
     * Fields to update
     * @var array
     */
    private $fields;

    /**
     * @var object
     */
    private $db;

    /**
     * @var object
     *
     * Made private now. Please use getStock() instead.
     */
    private $stock;

    /**
     * @var object
     */
    public $error;

    /**
     * @var object
     */
    public $keywords;

    /**
     * @var object
     */
    private $dbquery;

    /**
     * Constructor
     *
     * @param object  $kernel                The kernel object
     * @param integer $product_id            The product id
     * @param integer $old_product_detail_id If we want to find old details, e.g. for an invoice
     *
     * @return void
     */
    function __construct($kernel, $product_id = 0, $old_product_detail_id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Produkt-objektet kræver et Kernel-objekt.', E_USER_ERROR);
        }
        $this->kernel                = $kernel;
        $this->user                  = $kernel->user;
        $this->intranet              = $kernel->intranet;
        $this->db                    = new DB_Sql;
        $this->id                    = (int)$product_id;
        $this->old_product_detail_id = (int)$old_product_detail_id;
        $this->fields                = array('do_show', 'stock', 'has_variation');
        $this->error                 = new Intraface_Error;

        if ($this->id > 0) {
            $this->id = $this->load();
        }
    }

    /**
     * Creates the dbquery object

     * @return void
     */
    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }

        $this->dbquery = new Intraface_DBQuery($this->kernel, "product", "product.active = 1 AND product.intranet_id = ".$this->intranet->getId());
        $this->dbquery->setJoin("LEFT", "product_detail detail", "detail.product_id = product.id", "detail.active = 1");
        //$this->dbquery->setFindCharacterFromField("detail.name");
        $this->dbquery->useErrorObject($this->error);
        return $this->dbquery;
    }

    /**
     * Loads data into an array
     *
     * Should load both the specific product details, but also the product details
     *
     * @return integer product id or 0
     */
    public function load()
    {
        $this->db->query("SELECT id, active, locked, changed_date, ".implode(',', $this->fields)." FROM product
                WHERE intranet_id = " . $this->intranet->getId() . "
                    AND id = " . $this->id . " LIMIT 1");

        if (!$this->db->nextRecord()) {
            $this->value['id'] = 0;
            return 0;
        }

        // TODO HACK::HACK::HACK::HACK::HACK::HACK::HACK*
        //
        //  Vi bliver nødt til at hente value['id'] både før og efter,
        //  for når jeg kører arrayet fra produktdetaljerne ind i value
        //  så sletter det gamle array. Derfor hentes det før og efter
        //
        // HACK::HACK::HACK::HACK::HACK::HACK::HACK*

        // hente id
        $this->value['id'] = $this->db->f('id');

        // hente produktdetaljerne
        $this->detail = $this->getDetails();
        $this->value  = $this->detail->get();
        // hente id igen for ovenstående har overskrevet det
        $this->value['id']           = $this->db->f('id');
        $this->value['locked']       = $this->db->f('locked');
        $this->value['changed_date'] = $this->db->f('changed_date');
        $this->value['active']       = $this->db->f('active');

        // udtræk af produktdetaljer
        for ($i = 0, $max = count($this->fields); $i < $max; $i++) {
            $this->value[$this->fields[$i]] = $this->db->f($this->fields[$i]);
        }

        // We now remove stock from load. It can be obtained manaully!
        // $this->getStock();

        // desuden skal copy lige opdateres!
        // hvad med at vi bruger det øverste billede som primary. Det betyder dog, at
        // der skal laves noget position på AppendFile, men det er jo også smart nok.

        $this->value['id'] = $this->db->f('id');

        $this->db->free();
        return $this->value['id'];

    }

    /**
     * Gets the stock module
     *
     * @return object
     */
    public function getStock()
    {
        if ($this->hasVariation()) {
            throw new Exception('You cannot get stock from product with variations. Use stock for variation');
        }

        if ($this->value['stock'] == 0 AND $this->value['do_show'] == 1) {
            $this->value['stock_status'] = array('for_sale' => 100); // kun til at stock_status
        }
        // hvis det er en lagervare og intranettet har adgang til stock skal det startes op

        if ($this->kernel->intranet->hasModuleAccess('stock') AND $this->get('stock') == 1) {
            if(!is_object($this->stock)) {
                // hvis klassen ikke er startet op skal det ske
                $module = $this->kernel->useModule('stock', true); // true ignorere bruger adgang
                $this->stock                 = new Stock($this);
                $this->value['stock_status'] = $this->stock->get();
            }
            return $this->stock;
        }
        return false;
    }

    /**
     * Gets pictures for the product
     *
     * @return array
     */
    function getPictures()
    {
        $shared_filehandler = $this->kernel->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');

        $filehandler = new FileHandler($this->kernel);
        $append_file = new AppendFile($this->kernel, 'product', $this->get('id'));
        $appendix_list = $append_file->getList();

        $this->value['pictures'] = array();

        if (count($appendix_list) > 0) {
            foreach ($appendix_list AS $key => $appendix) {
                $tmp_filehandler = new FileHandler($this->kernel, $appendix['file_handler_id']);
                $this->value['pictures'][$key]['id']                   = $appendix['file_handler_id'];
                $this->value['pictures'][$key]['original']['icon_uri'] = $tmp_filehandler->get('icon_uri');
                $this->value['pictures'][$key]['original']['name']     = $tmp_filehandler->get('file_name');
                $this->value['pictures'][$key]['original']['width']    = $tmp_filehandler->get('width');
                $this->value['pictures'][$key]['original']['height']   = $tmp_filehandler->get('height');
                $this->value['pictures'][$key]['original']['file_uri'] = $tmp_filehandler->get('file_uri');
                $this->value['pictures'][$key]['appended_file_id']     = $appendix['id'];

                if ($tmp_filehandler->get('is_image')) {
                    $tmp_filehandler->createInstance();
                    $instances = $tmp_filehandler->instance->getList('include_hidden');
                    foreach ($instances AS $instance) {
                        $this->value['pictures'][$key][$instance['name']]['file_uri'] = $instance['file_uri'];
                        $this->value['pictures'][$key][$instance['name']]['name']     = $instance['name'];
                        $this->value['pictures'][$key][$instance['name']]['width']    = $instance['width'];
                        $this->value['pictures'][$key][$instance['name']]['height']   = $instance['height'];

                    }
                }
            }
        }
        return $this->value['pictures'];
    }

    /**
     * Validates
     *
     * @param array $array_var The array to validate
     *
     * @return boolean
     */
    private function validate($array_var)
    {
        if (!is_array($array_var)) {
            trigger_error('Product::save() skal have et array', E_USER_ERROR);
        }

        $validator = new Intraface_Validator($this->error);

        if (!$this->isNumberFree($array_var['number'])) {
            $this->error->set('Produktnummeret er ikke frit');
        }

        $validator->isNumeric($array_var['number'], 'Produktnummeret skal være et tal');
        settype($array_var['stock'], 'integer');
        $validator->isNumeric($array_var['stock'], 'stock', 'allow_empty');
        settype($array_var['do_show'], 'integer');
        $validator->isNumeric($array_var['do_show'], 'do_show', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    /**
     * Saves product
     *
     * @param array $array_var Array with details to save, @see $this->fields
     *
     * @return integer 0 on error
     */
    public function save($array_var)
    {
        if ($this->id > 0 AND $this->get('locked') == 1) {
            $this->error->set('Produktet er låst og kan ikke opdateres');
            return 0;
        }

        if (empty($array_var['number']) AND $this->get('number') > 0) {
            $array_var['number'] = $this->get('number');
        }

        if (empty($array_var['number'])) {
            $array_var['number'] = $this->getMaxNumber() + 1;
        }

        if (!$this->validate($array_var)) {
            return 0;
        }

        // lave sql-sætningen
        $sql = '';
        /*
        for ($i=0, $max = sizeof($this->fields); $i<$max; $i++) {
            if (!array_key_exists($this->fields[$i], $array_var)) {
                continue;
            }

            if (isset($array_var[$this->fields[$i]])) {
                $sql .= $this->fields[$i]." = '".safeToDb($array_var[$this->fields[$i]])."', ";
            } else {
                $sql .= $this->fields[$i]." = '', ";
            }
        }
        */

        foreach ($this->fields as $field) {
            if (!array_key_exists($field, $array_var)) {
                continue;
            }

            if (isset($array_var[$field])) {
                $sql .= $field." = '".safeToDb($array_var[$field])."', ";
            } else {
                $sql .= $field." = '', ";
            }
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end  = " WHERE id = " . $this->id . " AND intranet_id = " . $this->intranet->getId();
        } else {
            $sql_type = "INSERT INTO";
            $sql_end  = ", intranet_id = " . $this->intranet->getId();
        }

        $this->db->query($sql_type . " product SET ".$sql." changed_date = NOW()"	 . $sql_end);

        if (empty($this->id)) {
            $this->id = $this->db->insertedId();
        }

        $this->load();

        // gemme produktdetaljerne
        $product_detail = new ProductDetail($this);
        $product_detail->save($array_var);

        if ($this->error->isError()) {
            return 0;
        }
        $this->load();

        // should return id
        return $this->id;
    }

    /**
     * Copies product to a new product
     *
     * @return integer Id for the new product
     */
    public function copy()
    {
        $product = new Product($this->kernel);
        $product->getKeywords();

        $new_id = $product->save(
            array(
                'name' => $this->get('name') . ' (kopi)',
                'description' => $this->get('description'),
                'price' => amountToForm($this->get('price')), // make sure that this is formatted to local format
                'weight' => (float)$this->get('weight'),
                'unit' => $this->get('unit_key'),
                'vat' => $this->get('vat'),
                'state_account_id' => $this->get('state_account_id'),
                'stock' => $this->get('stock')
            )
        );

        // Relaterede produkter
        $related = $this->getRelatedProducts();
        if (is_array($related) AND count($related) > 0) {
            foreach ($related AS $p) {
                $product->setRelatedProduct($p['id']);
            }
        }

        // Nøgleord
        $appender = $this->getKeywordAppender();
        $this->getKeywords();
        $keywords = $appender->getConnectedKeywords();

        // @todo does not transfer keywords correctly
        if (is_array($keywords)) {
            $appender = $product->getKeywordAppender();
            foreach ($keywords AS $k) {
                $appender->addKeyword(new Keyword($this, $k['id']));
            }
        }

        // Billede
        $shared_filehandler = $this->kernel->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');

        $append_file = new AppendFile($this->kernel, 'product', $product->get('id'));

        $pictures = $this->getPictures();
        if (is_array($pictures)) {
            foreach ($pictures AS $pic) {
                $append_file->addFile(new FileHandler($this->kernel, $pic['id']));
            }
        }

        return $new_id;
    }

    /**
     * Deletes product
     *
     * Only set active to 0. Products must never be deleted from the database. It should always be
     * possible to go back to earlier products.
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->id == 0) {
            $this->error->set('Produktet kan ikke slettes, for produktid er ikke sat');
            return false;
        }
        if ($this->get('locked') == 1) {
            $this->error->set('Produktet kan ikke slettes, for det er låst.');
            return false;
        }

        $db = new Db_Sql;
        $sql = "UPDATE product
            SET active = 0
            WHERE id = " . $this->id. "
                AND intranet_id = " . $this->intranet->getId() . "
                AND locked = 0";
        $db->query($sql);

        $this->value['active'] = 0;
        return true;
    }

    /**
     * Undeletes a product
     *
     * @return boolean
     */
    public function undelete()
    {
        if ($this->id == 0) {
            $this->error->set('Produktet kan ikke findes igen, for produktid er ikke sat');

            return false;
        }
        $db = new Db_Sql;
        $sql = "UPDATE product
            SET active = 1
            WHERE id = " . $this->id. "
                AND intranet_id = " . $this->intranet->getId();
        $db->query($sql);
        $this->value['active'] = 1;
        return true;
    }

    /**
     * Returnerer det højeste produktnummer
     *
     * @return integer produktnummer
     */
    public function getMaxNumber()
    {
        $db = new DB_Sql;
        $sql = "SELECT product_detail.number
            FROM product
            INNER JOIN product_detail
                ON product_detail.product_id = product.id
            WHERE product.intranet_id = " . $this->intranet->getId() . "
            ORDER BY product_detail.number DESC LIMIT 1";
        $db->query($sql);
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f('number');
    }

    /**
     * Checks whether number is free
     *
     * @param integer $product_number Product number to check
     *
     * @return boolean
     */
    public function isNumberFree($product_number)
    {
        $product_number = (int)$product_number;

        $db = new DB_Sql;
         $sql = "SELECT product.id FROM product
          INNER JOIN product_detail detail
            ON product.id = detail.product_id
            WHERE detail.number = '" . $product_number . "'
                AND detail.product_id <> " . $this->id . "
                AND detail.active = 1
                AND product.active=1
                AND product.intranet_id = ".$this->intranet->getId()." LIMIT 1";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return true;
        }

        return false;

    }

    /**
     * Get keywords object
     *
     * @return object
     */
    public function getKeywords()
    {
        return ($this->keywords = new Keyword($this));
    }

    public function getKeywordAppender()
    {
        return new Intraface_Keyword_Appender($this);
    }

    /**
     * Set related product
     *
     * @param integer $id     Product id to relate to this product
     * @param string  $status Can be relate or remove
     *
     * @return boolean
     */
    public function setRelatedProduct($id, $status = 'relate')
    {
        if (empty($status)) $status = 'remove';

        $db = new DB_Sql;

        if ($status == 'relate') {
            $db->query("SELECT * FROM product_related WHERE product_id=" . $this->id  . " AND related_product_id = " . (int)$id . " AND intranet_id =" .$this->intranet->getId());
            if ($db->nextRecord()) return true;
            if ($id == $this->id) return false;
            $db->query("INSERT INTO product_related SET product_id = " . $this->id . ", related_product_id = " . (int)$id . ", intranet_id = " . $this->intranet->getId());
            return true;
        } else {
            $db->query("DELETE FROM product_related WHERE product_id = " . $this->id . " AND intranet_id = " . $this->intranet->getId() . " AND related_product_id = " . (int)$id);
            return true;
        }
    }

    /**
     * Delete related product
     *
     * @param integer $id     Product id to relate to this product
     *
     * @return boolean
     */
    public function deleteRelatedProduct($id)
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM product_related WHERE product_id = " . $this->id . " AND intranet_id = " . $this->intranet->getId() . " AND related_product_id = " . (int)$id);
        return true;
    }

    /**
     * Delete all related product
     *
     * @param integer $id     Product id to relate to this product
     *
     * @return boolean
     */
    public function deleteRelatedProducts()
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM product_related WHERE product_id = " . $this->id . " AND intranet_id = " . $this->intranet->getId());
        return true;
    }

    /**
     * Get all related products
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        $products = array();
        $key      = 0;
        $ids      = array();
        $db       = new DB_Sql;
        $sql      = "SELECT related_product_id FROM product_related WHERE product_id = " . $this->id . " AND intranet_id = " . $this->intranet->getId();
        $db->query($sql);

        // rækkefølgen er vigtig - først hente fra product og bagefter tilføje nye værdier til arrayet
        while ($db->nextRecord()) {
            $key                          = $db->f('related_product_id');
            $product                      = new Product($this->kernel, $db->f('related_product_id'));
            $products[$key]               = $product->get();
            $products[$key]['related_id'] = $db->f('related_product_id');

            if (!$product->hasVariation() AND is_object($product->getStock())) {
                $products[$key]['stock_status'] = $product->getStock()->get();
            } else {
                // alle ikke lagervarer der skal vises i webshop skal have en for_sale
                if ($product->get('stock') == 0 AND $product->get('do_show') == 1) {
                    $products[$key]['stock_status'] = array('for_sale' => 100); // kun til at stock_status
                } else {
                    $products[$key]['stock_status'] = array();
                }
            }
            // den her skal vist lige kigges igennem, for den tager jo alt med på nettet?
            // 0 = only stock
            if ($this->kernel->setting->get('intranet', 'webshop.show_online') == 0 AND !empty($which) AND $which=='webshop') { // only stock
                if (array_key_exists('for_sale', $products[$key]['stock_status']) AND $products[$key]['stock_status']['for_sale'] <= 0) {
                    continue;
                }
            }

        }
        return $products;
    }

    function hasVariation()
    {
        return $this->get('has_variation');
    }

    /**
     * Set attribute for product
     *
     * @param integer $id     Attribute id to relate to this product
     *
     * @return boolean
     */
    public function setAttributeGroup($id)
    {
        if(!$this->get('has_variation')) {
            throw new Exception('You can not set attribute group for a product without variations!');
        }

        $db = MDB2::factory(DB_DSN);
        $result = $db->query("SELECT id FROM product_x_attribute_group WHERE intranet_id = ".$db->quote($this->intranet->getId())." AND product_id=" . $this->getId()  . " AND product_attribute_group_id = " . (int)$id );
        if(PEAR::isError($result)) {
            throw new Exception('Error in query :'.$result->getUserInfo());
        }

        if ($result->numRows() > 0) return true;
        $result = $db->exec("INSERT INTO product_x_attribute_group SET product_id = " . $this->getId() . ", product_attribute_group_id = " . (int)$id . ", intranet_id = " . $this->intranet->getId());
        if(PEAR::isError($result)) {
            throw new Exception('Error in insert :'.$result->getUserInfo());
        }

        return true;
    }

    /**
     * Remove attribute for product
     *
     * @param integer $id     Attribute id to relate to this product
     *
     * @return boolean
     */
    public function removeAttributeGroup($id)
    {
        if(!$this->get('has_variation')) {
            throw new Exception('You can not remove attribute group for a product without variations!');
        }

        $db = MDB2::factory(DB_DSN);
        $result = $db->exec("DELETE FROM product_x_attribute_group WHERE intranet_id = ".$db->quote($this->intranet->getId())." AND product_id=" . $this->getId()  . " AND product_attribute_group_id = " . (int)$id );
        if(PEAR::isError($result)) {
            throw new Exception('Error in query :'.$result->getUserInfo());
        }

        return ($result > 0);

    }

    /**
     * Get all attributes related to the product
     *
     * @todo Rewrite product_x_attribute_group to Doctrine.
     * @todo Add a field named attribute_number to product_x_attribute_group, to be sure
     *       that a attribute always relates to the correct attribute number on the variation.
     *
     * @return array
     */
    public function getAttributeGroups()
    {
        if(!$this->get('has_variation')) {
            throw new Exception('You can not get attribute groups for a product without variations!');
        }

        // takes groups despite the are deleted. That is probably the best behaviour for now
        // NOTE: Very important that it is ordered by product_attribute_group.id so the groups
        // does always get attached to the correct attribute number on the variation. Se above todo in method doc
        $db = MDB2::factory(DB_DSN);
        $result = $db->query("SELECT product_attribute_group.* FROM product_x_attribute_group " .
                "INNER JOIN product_attribute_group " .
                    "ON product_x_attribute_group.product_attribute_group_id = product_attribute_group.id " .
                    "AND product_attribute_group.intranet_id = ".$db->quote($this->intranet->getId())." " .
                "WHERE product_x_attribute_group.intranet_id = ".$db->quote($this->intranet->getId())." " .
                    "AND product_x_attribute_group.product_id=" . $this->getId()  . " " .
                 "ORDER BY product_attribute_group.id");

        if(PEAR::isError($result)) {
            throw new Exception('Error in query :'.$result->getUserInfo());
        }

        return $result->fetchAll(MDB2_FETCHMODE_ASSOC);
    }


    /**
     * returns variation
     */
    public function getVariation($id = 0)
    {
        $gateway = new Intraface_modules_product_Variation_Gateway($this);
        if(intval($id) > 0) {
            return $gateway->findById($id);
        }
        $object = $gateway->getObject();
        $object->product_id = $this->getId();
        return $object;
    }

    /**
     * Returns variation on the basis of attributes
     *
     * @param array $attributes Attributes to find variation from
     *        array('attribte1' => [id1], 'attribute2' => [id2]);
     */
    public function getVariationFromAttributes($attributes)
    {
        $gateway = new Intraface_modules_product_Variation_Gateway($this);
        return $gateway->findByAttributes($attributes);
    }

    /**
     * Returns all variations on product
     */
    public function getVariations()
    {
        $gateway = new Intraface_modules_product_Variation_Gateway($this);
        return $gateway->findAll();
    }



    /**
     * Checks whether any products has been created before
     *
     * @return integer
     */
    public function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT count(*) AS antal FROM product WHERE intranet_id = " . $this->intranet->getId());
        if ($db->nextRecord()) {
            return $db->f('antal');
        }
        return 0;
    }

    /**
     * Checks whether there is any active products. Differs from isFilledIn() by checking for active = 1
     *
     * @return integer
     */
    public function any()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM product WHERE intranet_id = " . $this->intranet->getId()." AND active = 1");
        return $db->numRows();
    }

    public function isActive()
    {
        if ($this->value['active'] == 0) {
            return false;
        }

        return true;
    }

    /**
     * Public: Finde data til en liste
     *
     * Hvis den er fra webshop bør den faktisk opsamle oplysninger om søgningen
     * så man kan se, hvad folk er interesseret i.
     * Søgemaskinen skal være tolerant for stavefejl
     *
     * @param string $which valgfri søgeparameter - ikke aktiv endnu
     *
     * @return array indeholdende kundedata til liste
     */
    function getList($which = 'all')
    {
        switch ($this->getDBQuery()->getFilter('sorting')) {
            case 'date':
                    $this->getDBQuery()->setSorting("product.changed_date DESC");
                break;
            default:
                    $this->getDBQuery()->setSorting("detail.name ASC");
                break;
        }

        if ($search = $this->getDBQuery()->getFilter("search")) {
            $this->getDBQuery()->setCondition("detail.number = '".$search."'
                OR detail.name LIKE '%".$search."%'
                OR detail.description LIKE '%".$search."%'");
        }
        if ($keywords = $this->getDBQuery()->getFilter("keywords")) {
            $this->getDBQuery()->setKeyword($keywords);
        }

        if($this->getDBQuery()->checkFilter('shop_id') && $this->getDBQuery()->checkFilter('category')) {
            $category_type = new Intraface_Category_Type('shop', $this->getDBQuery()->checkFilter('shop_id'));
            $this->getDBQuery()->setJoin(
                'INNER',
                'ilib_category_append',
                'ilib_category_append.object_id = product.id',
                'ilib_category_append.intranet_id = '.$this->kernel->intranet->getId());
            $this->getDBQuery()->setJoin(
                'INNER',
                'ilib_category',
                'ilib_category_append.category_id = ilib_category.id',
                'ilib_category.intranet_id = '.$this->kernel->intranet->getId(). ' ' .
                    'AND ilib_category.belong_to = '.$category_type->getBelongTo().' ' .
                    'AND ilib_category.belong_to_id = '.$category_type->getBelongToId());

            $this->getDBQuery()->setCondition('ilib_category.id = '.$this->getDBQuery()->getFilter("category"));

        }

        if ($ids = $this->getDBQuery()->getFilter("ids")) {
            if (is_array($ids) && count($ids) > 0) {
                $this->getDBQuery()->setCondition("product.id IN (".implode(', ', $ids).")");
            } else {
                $this->getDBQuery()->setCondition('1 = 0');
            }
        }

        // @todo DEN OUTPUTTER IKKE DET RIGTIGE VED KEYWORD
        switch ($which) {
            case 'webshop':
                $this->getDBQuery()->setCondition("product.do_show = 1");
                break;
            case 'stock':
                $this->getDBQuery()->setCondition("product.stock = 1");
                break;
            case 'notpublished':
                $this->getDBQuery()->setCondition("product.do_show = 0");
                break;
            case 'all': // fall through
            default:
                $sql = '';
             break;
        }

        $i        = 0; // til at give arrayet en key
        $db       = $this->getDBQuery()->getRecordset("product.id", "", false);
        $products = array();

        while ($db->nextRecord()) {
            $product = new Product($this->kernel, $db->f("id"));
            $product->getPictures();
            $products[$i] = $product->get();

            if (!$product->get('has_variation') AND is_object($product->getStock()) AND strtolower(get_class($product->getStock())) == "stock") {
                $products[$i]['stock_status'] = $product->getStock()->get();
            } else {
                // alle ikke lagervarer der skal vises i webshop skal have en for_sale
                if ($product->get('stock') == 0 AND $product->get('do_show') == 1) {
                    $products[$i]['stock_status'] = array('for_sale' => 100); // kun til at stock_status
                } else {
                    $products[$i]['stock_status'] = array();
                }

            }

            // den her skal vist lige kigges igennem, for den tager jo alt med på nettet?
            // 0 = only stock
            if ($this->kernel->setting->get('intranet', 'webshop.show_online') == 0 AND $which=='webshop') { // only stock
                if (array_key_exists('for_sale', $products[$i]['stock_status']) AND $products[$i]['stock_status']['for_sale'] <= 0) {
                    continue;
                }
            }
            $i++;
        }
        $db->free();
        return $products;
    }

    /**
     * Gets id
     *
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Gets the details
     *
     * @return object
     */
    function getDetails()
    {
        return new ProductDetail($this, $this->old_product_detail_id);
    }

    /**
     * returns the possible units
     *
     * @return array units
     */
    public static function getUnits()
    {
        return ProductDetail::getUnits();
    }

}
