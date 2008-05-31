<?php
/**
 * Styrer adresser til intranet, bruger, kunde og kontaktperson
 *
 * Klassen kan styrer flere forskellige typer af adresser. Både for intranettet, brugere, kunder og kontaktpersoner.
 * Beskrivelsen af hvilke og med hvilket navn er beskrevet længere nede.
 *
 * @todo Skal vi programmere intranet_id ind i klassen? Det kræver at den får Kernel.
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 */
require_once 'Intraface/functions/functions.php';

class Address extends Intraface_Standard
{
    /**
     * @var integer
     */
    private $belong_to_key;

    /**
     * @var integer
     */
    private $belong_to_id;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    public $value = array();

    /**
     * @var array
     */
    public $fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

    /**
     * @var object error
     */
    public $error;

    /**
     * Init: loader klassen
     *
     * Her er angivet de typer af adresser den kan håndtere med arrayet address_type[].
     * $this-fields er felter i tabellen (db) som overføres til array og omvendt. Måske disse
     * engang skal differencieres, så man angvier hvad feltet i tabellen skal svare til navnet i arrayet.
     * Klassen loader også adressens felter
     *
     * @param integer $id Id on address.
     *
     * @return void
     */
    function __construct($id)
    {
        $this->id = $id;
        $this->error = new Intraface_Error;

        $this->load();

        $this->belong_to_types = $this->getBelongToTypes();

    }

    /**
     * Factory
     *
     * Returns an instace of Address from belong_to and belong_to_id
     *
     * @param string  $belong_to    What the address belongs to, corresponding to the ones in Address::getBelongToTypes()
     * @param integer $belong_to_id From belong_to. NB not id on the address
     *
     * @return object Address
     */
    function factory($belong_to, $belong_to_id)
    {
        $belong_to_types = Address::getBelongToTypes();

        $belong_to_key = array_search($belong_to, $belong_to_types);
        if ($belong_to_key === false) {
            trigger_error("Invalid address type '".$belong_to."' in Address::factory", E_USER_ERROR);
        }

        settype($belong_to_id, 'integer');
        if ($belong_to_id == 0) {
            trigger_error("Invalid belong_to_id in Address::factory", E_USER_ERROR);
        }

        $db = new DB_Sql;
        // intranet_id = ".$kernel->intranet->get('id')." AND
        $db->query("SELECT id FROM address WHERE type = ".$belong_to_key." AND belong_to_id = ".$belong_to_id." AND active = 1");
        if ($db->numRows() > 1) {
            trigger_error('There is more than one active address for '.$belong_to.':'.$belong_to_id.' in Address::facotory', E_USER_ERROR);
        }
        if ($db->nextRecord()) {
            return new Address($db->f('id'));
        } else {
            $address = new Address(0);
            $address->setBelongTo($belong_to, $belong_to_id);
            return $address;
        }
    }

    /**
     * Returns possible belong to types
     *
     * @return array
     */
    private static function getBelongToTypes()
    {
        return array(1 => 'intranet',
                     2 => 'user',
                     3 => 'contact',
                     4 => 'contact_delivery',
                     5 => 'contact_invoice',
                     6 => 'contactperson');
    }

    /**
     * Sets belong to @todo used for what?
     *
     * @param string  $belong_to    Which type the address belongs to
     * @param integer $belong_to_id Which id for the type the address belongs to
     *
     * @return void
     */
    function setBelongTo($belong_to, $belong_to_id)
    {

        if ($this->id != 0) {
            // is id already set, then you can not change belong_to
            return;
        }

        $belong_to_types = $this->getBelongToTypes();
        $this->belong_to_key = array_search($belong_to, $belong_to_types);
        if ($this->belong_to_key === false) {
            trigger_error("Invalid address type ".$belong_to." in Address::setBelongTo()", E_USER_ERROR);
        }

        $this->belong_to_id = (int)$belong_to_id;
        if ($this->belong_to_id == 0) {
            trigger_error("Invalid belong_to_id in Address::setBelongTo()", E_USER_ERROR);
        }
    }

    /**
     * Private: Loader data ind i array
     *
     * @return integer
     */
    private function load()
    {
        if ($this->id == 0) {
            return 0;
        }

        $db = MDB2::singleton(DB_DSN);
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $result = $db->query("SELECT id, type, belong_to_id, ".implode(', ', $this->fields)." FROM address WHERE id = ".(int)$this->id);

        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }

        if ($result->numRows() > 1) {
            trigger_error('There is more than one active address', E_USER_ERROR);
        }

        if ($result->numRows() == 0) {
            $this->id = 0;
            $this->value['id'] = 0;

            return 0;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

        $this->value = $row;
        $this->value['id'] = $row['id'];
        $this->value['address_id'] = $row['id'];
        $this->belong_to_key = $row['type'];
        $this->belong_to_id = $row['belong_to_id'];

        return $this->id;
    }

    /**
     * Validates
     *
     * @param array $array_var Values
     *
     * @return boolean
     */
    function validate($array_var)
    {
        $validator = new Intraface_Validator($this->error);
        if (empty($array_var)) {
            $this->error->set('array cannot be empty');
        }

        // public $fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

        settype($array_var['name'], 'string');
        $validator->isString($array_var['name'], 'there was an error in name', '');
        settype($array_var['address'], 'string');
        $validator->isString($array_var['address'], 'there was an error in address', '');
        settype($array_var['postcode'], 'string');
        $validator->isNumeric($array_var['postcode'], 'there was an error in postcode', 'greater_than_zero');
        settype($array_var['city'], 'string');
        $validator->isString($array_var['city'], 'there was an error in city', '');
        settype($array_var['country'], 'string');
        $validator->isString($array_var['country'], 'there was an error in country', '', 'allow_empty');
        settype($array_var['cvr'], 'string');
        $validator->isString($array_var['cvr'], 'there was an error in cvr', '', 'allow_empty');
        // E-mail is not allowed to be empty do you need that. You should probably consider some places there this is needed before you set it (eg. intranet and user address) maybe make a param more to the function determine that: 'email:allow_empty'
        settype($array_var['email'], 'string');
        $validator->isEmail($array_var['email'], 'not a valid e-mail');
        settype($array_var['website'], 'string');
        $validator->isUrl($array_var['website'], 'website is not valid', '', 'allow_empty');
        settype($array_var['phone'], 'string');
        $validator->isString($array_var['phone'], 'not a valid phone number', '', 'allow_empty');
        settype($array_var['ean'], 'string');
        $validator->isString($array_var['ean'], 'ean location number is not valid', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Public: Denne funktion gemmer data. At gemme data vil sige, at den gamle adresse gemmes, men den nye aktiveres.
     *
     * @param array $array_var et array med felter med adressen. Se felterne i init funktionen: $this->fields
     *
     * @return bolean	true or false
     */
    function save($array_var)
    {

        if ($this->belong_to_key == 0 || $this->belong_to_id == 0) {
            trigger_error("belong_to or belong_to_id was not set. Maybe because the provided address id was not valid. In Address::save", E_USER_ERROR);
        }

        $db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($db)) {
            trigger_error("Error db singleton: ".$db->getUserInfo(), E_USER_ERROR);
            return false;
        }
        $sql = '';

        if (count($array_var) > 0) {
            if ($this->id != 0) {
                $do_update = 0;
                foreach ($this->fields AS $i => $field) {
                    if (array_key_exists($field, $array_var) AND isset($array_var[$field])) {
                        $sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
                        if ($this->get($field) != $array_var[$field]) {
                            $do_update = 1;
                        }
                    }
                }
            } else {
                // Kun hvis der rent faktisk gemmes nogle værdier opdaterer vi. hvis count($arra_var) > 0 så må der også være noget at opdatere?
                $do_update = 0;
                foreach ($this->fields AS $i => $field) {
                    if (array_key_exists($field, $array_var) AND isset($array_var[$field])) {
                        $sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
                        $do_update = 1;
                    }
                }
            }

            if ($do_update == 0) {
                // There is nothing to save, but that is OK, so we just return 1
                return true;
            } else {
                $result = $db->exec("UPDATE address SET active = 0 WHERE type = ".$this->belong_to_key." AND belong_to_id = ".$this->belong_to_id);
                if (PEAR::isError($result)) {
                    trigger_error("Error in exec: ".$result->getUserInfo(), E_USER_ERROR);
                    return false;
                }

                $result = $db->exec("INSERT INTO address SET ".$sql." type = ".$this->belong_to_key.", belong_to_id = ".$this->belong_to_id.", active = 1, changed_date = NOW()");
                if (PEAR::isError($result)) {
                    trigger_error("Error in exec: ".$result->getUserInfo(), E_USER_ERROR);
                    return false;
                }
                $this->id = $db->lastInsertId('address', 'id');
                $this->load();
                return true;
            }
        } else {
            // Der var slet ikke noget indhold i arrayet, så vi lader være at opdatere, men siger, at vi gjorde.
            return true;
        }
    }

    /**
     * Public: Opdatere en adresse.
     *
     * UPDATE: Metoden er udkommenteret fra 18/10 2007 da den ikke ser ud til at blive benyttet!
     *
     * Denne funktion overskriver den nuværende adresse. Benyt som udagangspunkt ikke denne, da historikken på adresser skal gemmes.
     *
     * @param array $array_var et array med felter med adressen. Se felterne i init funktionen: $this->fields
     *
     * @return integer Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme på en old_address.
     */
    /*
    function update($array_var) {
        if ($this->id == 0) {
            trigger_error("id has to be set to use Address::update, maybe you want to use Address::save IN Address->update", E_USER_ERROR);
        }

        $db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($db)) {
            trigger_error("Error db singleton: ".$db->getUserInfo(), E_USER_ERROR);
            return false;
        }

        foreach ($this->fields AS $i => $field) {
            $sql = '';
            if (isset($array_var[$field])) {
                $sql .= $field." = ".$db->quote($array_var[$field]).", ";
            }
        }

        $result = $db->exec("UPDATE address SET ".$sql." changed_date = NOW() WHERE id = ".$this->id);
        if (PEAR::isError($result)) {
            trigger_error("Error in exec: ".$result->getUserInfo(), E_USER_ERROR);
            return false;
        }
        $this->load();
        return 1;
    }
    */
}
?>
