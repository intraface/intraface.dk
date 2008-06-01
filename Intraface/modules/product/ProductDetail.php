<?php
/**
 * Styrer varer
 *
 * @package Intraface_Product
 * @version 001
 * @author Lars Olesen <lars@legestue.net>
 *
 * TODO Lige nu gemmer den altid en ny produktdetalje uanset, hvad jeg gør.
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
            trigger_error('ProductDetail-objektet kræver et Product-objekt.', E_USER_ERROR);
        }

        $this->product       = $product;
        $this->db            = new Db_sql;
        $this->old_detail_id = (int)$old_detail_id;
        $this->fields        = array('number', 'name', 'description', 'price', 'unit', 'do_show', 'vat', 'weight', 'state_account_id');
        $this->detail_id     = $this->load();
    }

    /**
     * Loads details into an array
     *
     * @return integer product detail id on success or zero
     */
    private function load()
    {
        if ($this->old_detail_id != 0) {
            $sql = "id = ".$this->old_detail_id;
        } else {
            $sql = "active = 1";
        }

        $sql = "SELECT id, ".implode(',', $this->fields)." FROM product_detail WHERE ".$sql . "
            AND product_id = " . $this->product->get('id');
        $this->db->query($sql);
        if ($this->db->numRows() > 1) {
            trigger_error('Systemfejl', 'Der er mere end en aktiv produktdetalje', E_USER_ERROR);
        } elseif ($this->db->nextRecord()) {
            // hardcoded udtræk af nogle vigtige oplysnigner, som vi ikke kan have i feltlisten
            for ($i = 0, $max = count($this->fields); $i<$max; $i++) {
                $this->value[$this->fields[$i]] = $this->db->f($this->fields[$i]);
            }

            // unit skal skrives om til den egentlige unit alt efter settings i produkterne
            $this->value['detail_id'] = $this->db->f('id');
            $this->value['unit_id']   = $this->db->f('unit');



            $unit = $this->getUnits($this->db->f('unit'));
            if (empty($unit)) {
                trigger_error('invalid unit '.$this->db->f('unit').'!', E_USER_ERROR);
                exit;
            }

            $this->value['unit_key'] = $this->db->f('unit');
            $this->value['unit']     = $unit;

            // udregne moms priser ud fra prisen, men kun hvis der er moms på den
            if ($this->db->f('vat') == 1) {
                $this->value['price_incl_vat'] = (float)$this->db->f('price') + ($this->db->f('price') * 0.25);
            } else {
                $this->value['price_incl_vat'] = (float)$this->db->f('price');
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


        if ($this->old_detail_id != 0) {
            // save kan ikke bruges hvis man skal opdatere et gammelt produkt
            // men så bør den vel bare automatisk kalde update(), som i øjeblikket
            // er udkommenteret.
            return false;
        } elseif (count($array_var) == 0) {
            // Der er ikke noget indhold i arrayet
            return false;
        }

        $this->db->query("SELECT * FROM product_detail WHERE id = ".$this->detail_id . "
                AND product_id = " . $this->product->get('id'));

        if ($this->db->nextRecord()) {
            // her skal vi sørge for at få billedet med
            $do_update = 0;
            $sql       = '';
            /*
            for ($i=0, $max = sizeof($this->fields); $i<$max; $i++) {
                if (!array_key_exists($this->fields[$i], $array_var)) {
                    continue;
                }
                if (isset($array_var[$this->fields[$i]])) {
                    $sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
                } else {
                    $sql .= $this->fields[$i]." = '', ";
                }
                if (isset($array_var[$this->fields[$i]]) AND $this->db->f($this->fields[$i]) != $array_var[$this->fields[$i]] OR (is_numeric($this->db->f($this->fields[$i]) AND $this->db->f($this->fields[$i])) > 0)) {
                    $do_update = 1;
                }
            }
            */
            foreach ($this->fields as $field) {
                /*
                if (!array_key_exists($field, $array_var)) {
                    continue;
                }
                */
                if (!empty($array_var[$field])) {
                    $sql .= $field." = '".$array_var[$field]."', ";
                } elseif (isset($array_var[$field])) {
                    $sql .= $field . " = '', ";
                } else {
                    // why continue? /sune 3/12 2007
                    // continue;
                    // this might be another prober solution also for saving all known details
                    // but we need to be aware of number also!
                    $sql .= $field . " = '".$this->db->f($field)."', ";
                }
                if (isset($array_var[$field]) AND $this->db->f($field) != $array_var[$field] OR (is_numeric($this->db->f($field) AND $this->db->f($field)) > 0)) {
                    $do_update = 1;
                }

            }
            if ($this->db->f('pic_id') > 0) {
                $picture_id = $this->db->f('pic_id');
            }
        } else {
            // der er ikke nogen tidligere poster, så vi opdatere selvfølgelig
            $do_update = 1;
            $sql       = '';
            // we make sure that unit is set to a valid unit.
            if (empty($array_var['unit'])) {
                $array_var['unit'] = 1;
            }
            /*
            for ($i=0, $max = sizeof($this->fields), $sql = ''; $i<$max; $i++) {
                if (!array_key_exists($this->fields[$i], $array_var)) {
                    continue;
                }
                if (isset($array_var[$this->fields[$i]])) {
                    $sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
                } else {
                    $sql .= $this->fields[$i]." = '', ";
                }
            }
            */
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
            // Hmmmmm, der er slet ikke nogen felter der er ændret! Så gemmer vi ikke, men siger at det gik godt :-)
            return true;
        } else {

            // vi opdaterer produktet
            $this->db->query("UPDATE product_detail SET active = 0 WHERE product_id = " . $this->product->get('id'));
            $this->db->query("INSERT INTO product_detail SET ".$sql." active = 1, changed_date = NOW(), product_id = " . $this->product->get('id') . ", intranet_id = " . $this->product->kernel->intranet->get('id'));
            $this->detail_id = $this->db->insertedId();

            $this->load();
            $this->product->load();

            $this->old_detail_id = $this->detail_id;

            return true;
        }
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
