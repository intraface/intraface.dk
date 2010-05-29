<?php
/**
 * Styrer varer
 *
 * @package Intraface_Product
 * @version 001
 * @author Lars Olesen <lars@legestue.net>
 *
 * TODO Lige nu gemmer den altid en ny produktdetalje uanset, hvad jeg g�r.
 */
class ProductDetail extends Intraface_Standard
{
    /**
     * @var array
     */
    public $value = array();

    /**
     * @var array
     */
    private $fields; // tabelfelter

    /**
     * @var integer
     */
    private $detail_id;

    /**
     * @var integer
     */
    private $old_detail_id;

    /**
     * @var object
     */
    private $product;

    /**
     * @var object
     */
    private $db;

    /**
     * Constructor
     *
     * @param object  $product       Product object
     * @param integer $old_detail_id Only used with old product details
     *
     * @return void
     */
    public function __construct($product, $old_detail_id = 0)
    {
        if (!is_object($product)) {
            trigger_error('ProductDetail-objektet kr�ver et Product-objekt.', E_USER_ERROR);
        }

        $this->product       = $product;
        $this->db            = new DB_Sql;
        $this->old_detail_id = (int)$old_detail_id;
        $this->fields        = array('number', 'price', 'before_price', 'do_show', 'vat', 'weight', 'state_account_id');
        $this->detail_id     = $this->load();
    }

    function getNumber()
    {
        return $this->get('number');
    }

    /**
     * Loads details into an array
     *
     * @return integer product detail id on success or zero
     */
    private function load()
    {
        if ($this->old_detail_id != 0) {
            $sql = "product_detail.id = ".$this->old_detail_id;
        } else {
            $sql = "active = 1";
        }

        $sql = "SELECT product_detail.id, product_detail.unit AS unit_key,".implode(',', $this->fields).", product_detail_translation.name, product_detail_translation.description FROM product_detail
            LEFT JOIN product_detail_translation ON product_detail.id = product_detail_translation.id AND product_detail_translation.lang = 'da'
            WHERE ".$sql . "
            AND product_id = " . $this->product->get('id') . ' AND intranet_id = ' . $this->product->intranet->getId();
        $this->db->query($sql);
        if ($this->db->numRows() > 1) {
            trigger_error('Der er mere end en aktiv produktdetalje', E_USER_ERROR);
        } elseif ($this->db->nextRecord()) {
            // hardcoded udtr�k af nogle vigtige oplysnigner, som vi ikke kan have i feltlisten
            for ($i = 0, $max = count($this->fields); $i<$max; $i++) {
                $this->value[$this->fields[$i]] = $this->db->f($this->fields[$i]);
            }
            $this->value['name'] = $this->db->f('name');
            $this->value['description'] = $this->db->f('description');
            $this->value['unit_key'] = $this->db->f('unit_key');

            // unit skal skrives om til den egentlige unit alt efter settings i produkterne
            $this->value['detail_id'] = $this->db->f('id');

            $unit = $this->getUnits($this->db->f('unit_key'));
            if (empty($unit)) {
                trigger_error('invalid unit '.$this->db->f('unit_key').'!', E_USER_ERROR);
                exit;
            }

            $this->value['unit_id']   = $this->db->f('unit_key');
            $this->value['unit']     = $unit;

            // udregne moms priser ud fra prisen, men kun hvis der er moms p� den
            if ($this->db->f('vat') == 1) {
                $this->value['vat_percent'] = 25;
                $this->value['price_incl_vat'] = round((float)$this->db->f('price') + ((float)$this->db->f('price') * 0.25), 2);
            } else {
                $this->value['vat_percent'] = 0;
                $this->value['price_incl_vat'] = round((float)$this->db->f('price'), 2);
            }
            return $this->db->f('id');
        } else {
            return 0;
        }
    }

    /**
     * Validates
     *
     * @param array $array_var Details to validate
     *
     * @return boolean
     */
    private function validate($array_var)
    {
        $validator = new Intraface_Validator($this->product->error);
        $validator->isString($array_var['name'], 'Du har brugt ulovlige tegn i beskrivelsen');
        $validator->isString($array_var['description'], 'Du har brugt ulovlige tegn i beskrivelsen', '<strong><em>', 'allow_empty');
        $validator->isNumeric($array_var['unit'], 'Fejl i unit');

        $validator->isNumeric($array_var['state_account_id'], 'Fejl i state_account', 'allow_empty');
        $validator->isNumeric($array_var['do_show'], 'Fejl i do_show', 'allow_empty');
        $validator->isNumeric($array_var['vat'], 'Fejl i vat');
        $validator->isNumeric($array_var['pic_id'], 'Fejl i billedid', 'allow_empty');
        $validator->isNumeric($array_var['weight'], 'Fejl i vægt - skal være et helt tal', 'allow_empty');

        if (isset($array_var['price'])) $validator->isNumeric($array_var['price'], 'Fejl i pris', 'allow_empty');
        if (isset($array_var['before_price'])) $validator->isNumeric($array_var['before_price'], 'Fejl i førpris', 'allow_empty');

        if ($this->product->error->isError()) {
            return false;
        }

        return true;
    }

    /**
     * Saves data
     *
     * The old address is saved and deactivated, while the new details are activated. Data should never
     * be saved on an old product detail id.
     *
     * @param array $array_var An array with data to save, @see $this->fields
     *
     * @return integer
     */
    public function save($array_var)
    {
        $array_var = safeToDb($array_var);

        if (isset($array_var['price'])) {
            $amount = new Intraface_Amount($array_var['price']);
            $amount->convert2db();
            $array_var['price'] = $amount->get();
        }

        if (isset($array_var['before_price'])) {
            $amount = new Intraface_Amount($array_var['before_price']);
            $amount->convert2db();
            $array_var['before_price'] = $amount->get();
        }


        if ($this->old_detail_id != 0) {
            // save kan ikke bruges hvis man skal opdatere et gammelt produkt
            // men s� b�r den vel bare automatisk kalde update(), som i �jeblikket
            // er udkommenteret.
            return false;
        } elseif (count($array_var) == 0) {
            // Der er ikke noget indhold i arrayet
            return false;
        }

        // $this->db->query("SELECT * FROM product_detail WHERE id = ".$this->detail_id . "
        //         AND product_id = " . $this->product->get('id') . ' AND intranet_id = ' . $this->product->intranet->getId());

        if ($this->detail_id != 0) { // $this->db->nextRecord()
            // her skal vi s�rge for at f� billedet med
            $do_update = 0;
            $sql       = '';

            // check if there is any changes and only update changes.
            foreach ($this->fields as $field) {
                if (isset($array_var[$field])) {
                    if ($this->get($field) != $array_var[$field]) {
                        $do_update = 1;
                    }

                    $sql .= $field." = '".$array_var[$field]."', ";
                } else {
                    $sql .= $field." = '".$this->get($field)."', ";
                }
            }

            if(isset($array_var['unit'])) {
                if($array_var['unit'] != $this->get('unit_key')) {
                    $do_update = 1;
                }
                $sql .= "unit = '".$array_var['unit']."', ";
            } else {
                $sql .= "unit = '".$this->get('unit_key')."', ";
            }

            if(isset($array_var['name'])) {
                if($array_var['name'] != $this->get('name')) {
                    $do_update = 1;
                }
            } else {
                $array_var['name'] = $this->get('name');
            }

            if(isset($array_var['description'])) {
                if($array_var['description'] != $this->get('description')) {
                    $do_update = 1;
                }
            } else {
                $array_var['description'] = $this->get('description');
            }


        } else {
            // der er ikke nogen tidligere poster, s� vi opdatere selvf�lgelig
            $do_update = 1;
            $sql       = '';
            // we make sure that unit is set to a valid unit.
            if (empty($array_var['unit'])) $array_var['unit'] = 1;
            $sql .= "unit = ".intval($array_var['unit']).", ";

            if(!isset($array_var['name'])) $array_var['name'] = '';
            if(!isset($array_var['description'])) $array_var['description'] = '';

            foreach ($this->fields as $field) {
                if (!array_key_exists($field, $array_var)) {
                    continue;
                }
                if (!empty($array_var[$field])) {
                    $sql .= $field." = '".$array_var[$field]."', ";
                } elseif (isset($array_var[$field])) {
                    $sql .= $field." = '', ";
                } else {
                    continue;
                }
            }
        }

        if ($do_update == 0) {
            // Hmmmmm, der er slet ikke nogen felter der er �ndret! S� gemmer vi ikke, men siger at det gik godt :-)
            return true;
        } else {

            // vi opdaterer produktet
            $this->db->query("UPDATE product_detail SET active = 0 WHERE product_id = " . $this->product->get('id'));
            $this->db->query("INSERT INTO product_detail SET ".$sql." active = 1, changed_date = NOW(), product_id = " . $this->product->get('id') . ", intranet_id = " . $this->product->intranet->getId());
            $this->detail_id = $this->db->insertedId();
            $this->db->query("INSERT INTO product_detail_translation SET name = \"".$array_var['name']."\", description = \"".$array_var['description']."\", lang = 'da', id = ".$this->detail_id);


            $this->load();
            $this->product->load();

            $this->old_detail_id = $this->detail_id;

            return true;
        }
    }

    public function getPrice()
    {
        return new Ilib_Variable_Float($this->get('price'));
    }

    public function getPriceInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->get('price') / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100), 2), 'iso');
    }

    public function getPriceIncludingVat()
    {
        return new Ilib_Variable_Float(round($this->get('price') * (1 + $this->getVatPercent()->getAsIso()/100), 2));
    }

    public function getPriceIncludingVatInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->get('price') / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100) * (1 + $this->getVatPercent()->getAsIso()/100), 2), 'iso');
    }

    public function getBeforePrice()
    {
        return new Ilib_Variable_Float($this->get('before_price'));
    }

    public function getBeforePriceInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->get('price') / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100), 2), 'iso');
    }

    public function getBeforePriceIncludingVat()
    {
        return new Ilib_Variable_Float(round($this->get('before_price') * (1 + $this->getVatPercent()->getAsIso()/100), 2));
    }

    public function getBeforePriceIncludingVatInCurrency($currency, $exchange_rate_id = 0)
    {
        return new Ilib_Variable_Float(round($this->get('before_price') / ($currency->getProductPriceExchangeRate((int)$exchange_rate_id)->getRate()->getAsIso() / 100) * (1 + $this->getVatPercent()->getAsIso()/100), 2), 'iso');
    }

    public function getVatPercent()
    {
        return new Ilib_Variable_Float($this->value['vat_percent']);
    }

    /**
     * Gets the corresponding unit to a key
     *
     * @param string $key The unit key
     *
     * @return array
     */
    public static function getUnits($key = null)
    {
        $units = array(1 => array('singular' => '',
                                  'plural' => '',
                                  'combined' => ''),
                       2 => array('singular' => 'unit',
                                  'plural' => 'units',
                                  'combined' => 'unit(s)'),
                       3 => array('singular' => 'day',
                                  'plural' => 'days',
                                  'combined' => 'day(s)'),
                       4 => array('singular' => 'month (singular)',
                                  'plural' => 'month (plural)',
                                  'combined' => 'month (combined)'),
                       5 => array('singular' => 'year',
                                  'plural' => 'years',
                                  'combined' => 'year(s)'),
                       6 => array('singular' => 'hour',
                                  'plural' => 'hours',
                                  'combined' => 'hour(s)')
                 );

        if ($key === null) {
            return $units;
        } else {
            if (!empty($units[$key])) {
                return $units[$key];
            } else {
                return '';
            }
        }
    }

    function setStateAccountId($id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE product_detail SET state_account_id = ' . $id . ' WHERE id = ' . $this->detail_id);
        $this->load();
        $this->product->load();
        return true;
    }
}
