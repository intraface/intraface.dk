<?php
/**
 * Handles a contact
 *
 * I think we should distinguish between company and contact
 *

 // www, email, cvr, ean hoerer ikke hertil
 abstract class Address
 {
 private $types;

 // istedet for belong_to og belong_to_id
 // skal vaere den samme maade som tags

 public function __construct($data) {
 $this->init();
 // data is put into variables
 // this can be done either from a database or from some form input (remember to validate from forminput)
 }

 function registerType($id, $type)
 {
 // some rules for id and type
 $this->types[$id] = $type;
 }

 function getTypes()
 {
 return $this->types;
 }

 abstract function init();

 }

 // this is the one we use for our application
 class MyAddress extends Address
 {
 function init()
 {
 $this->registerType(1, 'intranet');
 }
 }

 class Contact
 {
 public $name;
 public $occupation:
 public $birthday;

 // et eller andet med et keyword
 // work, home, delivery, billing etc.
 function addAddress() {}

 function setPrimaryAddress() {}

 function getPrimaryAddress() {}

 function getAddresses() {}

 ////////////////////////////

 function addEmail() {}

 function getEmails() {}

 function setPrimaryEmail() {}

 function getPrimaryEmail() {}

 ////////////////////////////

 function getBirthday() { // return date object }

 /////////////////////////////

 function addPicture() {}

 function setPrimaryPicture() {}

 function getPictures() {}

 ///////////////////////////////

 function addWebsite() {}

 function getWebsites() {}

 ////////////////////////////////

 // maaske med mobile eller landline, work
 function addPhone() {}

 function getPhones() {}

 }

 class Company extends Contact
 {
 public $ean;
 public $cvr;

 function addContact() {}

 function getContacts()
 }



 *
 * @package Intraface_Contact
 * @author	Lars Olesen <lars@legestue.net>
 * @author	Sune Jensen <sj@sunet.dk>
 * @version	1.0
 * @since	1.0
 * @access Public
 * @copyright 	Lars Olesen
 * @license
 *
 */
require_once dirname(__FILE__) . '/ContactPerson.php';
require_once 'Intraface/functions.php';

class Contact extends Intraface_Standard
{
    /**
     * @var object
     */
    public $kernel;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    public $value;

    /**
     * @var object
     */
    public $address;

    /**
     * @var object
     */
    public $delivery_address;

    /**
     * @var object
     */
    public $error;

    /**
     * @var object
     */
    public $contactperson;

    /**
     * @var object
     */
    private $message;

    /**
     * @var object
     */
    public $keywords;

    /**
     * @var object
     */
    private $lock;

    /**
     * @var array
     */
    private $addresses = array(0 => 'standard',
    1 => 'delivery',
    2 => 'invoice');

    /**
     * @var array
     */
    private $types = array(0 => 'private',
    1 => 'corporation');

    /**
     * @todo has to be made private
     */
    public $dbquery;

    /**
     * Constructor
     *
     * @param object  $kernel
     * @param integer $id	  Contact id
     *
     * @return	void
     */
    public function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Contact kr�ver kernel - fik ' . get_class($kernel), E_USER_ERROR);
        }
        $this->kernel = $kernel;
        //$contact_module = $this->kernel->getModule('contact');
        //$this->types = $contact_module->getSetting('type');

        $this->error = new Intraface_Error;
        $this->id = (int)$id;

        $this->fields = array('type_key', 'paymentcondition', 'number', 'preferred_invoice', 'openid_url', 'username', 'code');

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getError()
    {
        return $this->error;
    }

    public function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        $this->dbquery = new Intraface_DBQuery($this->kernel, "contact", "contact.active = 1 AND contact.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id", "address.active = 1 AND address.type = 3");
        $this->dbquery->useErrorObject($this->error);

        return $this->dbquery;
    }

    /**
     * Constructor
     *
     * @param object $kernel
     * @param string $type   What should the contact object be created from
     * @param string $id     The value which corresponds to the type
     *
     * @return  object
     */
    public function factory($kernel, $type, $value)
    {
        // Husk noget validering af de forskellige values og typer
        $gateway = new Intraface_modules_contact_ContactGateway($kernel, new DB_Sql);
        try {
            switch($type) {
                case 'email':
                    return $gateway->findByEmail($value);
                case 'code':
                    return $gateway->findByCode($value);
                case 'username':
                    return $gateway->findByUsername($value);
                case 'openid_url':
                    return $gateway->findByOpenId($value);
                    // Her b�r vel v�re et tjek p� hvor mange - og hvis mange give en fejl
                    break;
                default:
                    trigger_error('Contact::factory() skal bruge en type');
                    break;
            }
        } catch (Exception $e) {
            return $contact = new Contact($kernel);
        }

        /*
         $db = new DB_Sql;
         switch($type) {
         case 'email':
         $db->query("SELECT address.belong_to_id AS id FROM contact INNER JOIN address ON address.belong_to_id = contact.id WHERE address.email = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id') . " AND address.active = 1 AND contact.active = 1");
         break;
         case 'code':
         $db->query("SELECT id FROM contact WHERE code  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
         break;
         case 'username':
         $db->query("SELECT id FROM contact WHERE username  = '".$value['username']."' AND password  = '".$value['password']."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
         break;
         case 'openid_url':
         $db->query("SELECT id FROM contact WHERE openid_url  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
         // Her b�r vel v�re et tjek p� hvor mange - og hvis mange give en fejl
         break;
         default:
         trigger_error('Contact::factory() skal bruge en type');
         break;
         }
         if (!$db->nextRecord()) {
         return $contact = new Contact($kernel);
         }
         $id = $db->f('id');

         return ($contact = new Contact($kernel, $id));
         */
    }

    /**
     * Loads values for the contact into an array
     *
     * @return true on success
     */
    private function load()
    {
        $db = new DB_Sql;
        $this->value = array();

        $db->query("SELECT id, ".implode(',', $this->fields).", password
            FROM contact
            WHERE contact.id=".$this->id."
                AND intranet_id =".$this->kernel->intranet->get('id'));

        if (!$db->nextRecord()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return false;
        }

        $this->value['id'] = $db->f('id');

        foreach ($this->fields as $field) {
            $this->value[$field] = $db->f($field);
        }
        $this->value['type_key'] = $db->f('type_key');
        $this->value['type'] = $this->getType();
        $this->value['password'] = $db->f('password');
        $this->value['username'] = $db->f('username');
        $this->value['number'] = $db->f('number');
        $this->value['code'] = $db->f('code');

        if ($this->get('type') == 'corporation') {
            $this->contactperson = new ContactPerson($this);
        }

        $this->address = Intraface_Address::factory('contact', $db->f('id'));
        $this->delivery_address = Intraface_Address::factory('contact_delivery', $db->f('id'));

        // name m� ikke fjernes - bruges af keywords
        $this->value['name'] = $this->address->get('name');
        $this->value['openid_url'] = $this->get('openid_url');

        $this->value['id'] = $db->f('id'); // m� ikke fjernes

        return true;
    }

    /**
     * Gets the address
     *
     * @return object
     */
    public function getAddress()
    {
        if (!is_object($this->address)) {
            $this->address = Intraface_Address::factory('contact', $this->id);
        }
        return $this->address;
    }

    /**
     * Gets the login url for the contact
     *
     * @return string
     */
    public function getLoginUrl()
    {
        // HACK NECCESSARY FOR THE NEWSLETTERSUBSCRIBER
        $this->kernel->useModule('contact');
        return ($this->value['login_url'] = 'http://' . $this->kernel->getSetting()->get('intranet', 'contact.login_url') . '/' .$this->kernel->intranet->get('identifier') . '/login?code='. $this->get('code'));
    }

    /**
     * Validates values
     *
     * @param array $var Values to validate
     *
     * @return true on success
     */
    public function validate($var)
    {
        $var = $var;

        if (array_key_exists('number', $var) AND !$this->isNumberFree($var['number'])) {
            $this->error->set('Kundenummeret er ikke frit');
        }

        $validator = new Intraface_Validator($this->error);
        if (!empty($var['type_key'])) {
            $validator->isNumeric($var['type_key'], 'Fejl i typen', 'allow_empty');
        }
        if (!empty($var['openid_url'])) {
            $validator->isUrl($var['openid_url'], 'Openid_url', 'allow_empty');
        }

        // address
        $validator->isString($var['name'], 'Navnet er ikke en gyldig streng');
        if (!empty($var['address'])) {
            $validator->isString($var['address'], 'Adressen er ikke en streng', '', 'allow_empty');
        }
        if (!empty($var['postcode'])) {
            $validator->isString($var['postcode'], 'Postkoden er ikke gyldig', '', 'allow_empty');
        }
        if (!empty($var['city'])) {
            $validator->isString($var['city'], 'Byen er ikke en by', '', 'allow_empty');
        }
        if (!empty($var['country'])) {
            $validator->isString($var['country'], 'Der er fejl i landet', '', 'allow_empty');
        }
        if (!empty($var['phone'])) {
            $validator->isString($var['phone'], 'Telefonnummeret et ikke rigtigt.', '', 'allow_empty');
        }
        if (!empty($var['email'])) {
            $validator->isEmail($var['email'], 'E-mailen er ikke en rigtig e-mail', 'allow_empty');
        }
        if (!empty($var['website'])) {
            $validator->isUrl($var['website'], 'Der er fejl i urlen', 'allow_empty');
        }
        if (!empty($var['cvr'])) {
            $validator->isNumeric($var['cvr'], 'Fejl i cvr-nummeret', 'allow_empty');
        }
        settype($var['ean'], 'string');
        if (!empty($var['ean'])) {
            $validator->isNumeric($var['ean'], 'Fejl i ean-nummeret', 'allow_empty');
        }

        if (!empty($var['ean']) AND strlen($var['ean']) != 13) {
            $this->error->set('EAN-nummeret skal pr�cis v�re 13 tal');
        }

        //deliveryaddress
        if (!empty($var['delivery_name'])) {
            $validator->isString($var['delivery_name'], 'Leveringsadressen forkert', '', 'allow_empty');
        }
        if (!empty($var['delivery_address'])) {
            $validator->isString($var['delivery_address'], 'Leveringsadressen forkert', '', 'allow_empty');
        }
        if (!empty($var['delivery_postcode'])) {
            $validator->isString($var['delivery_postcode'], 'Leveringsadressens postnummer', '', 'allow_empty');
        }
        if (!empty($var['delivery_city'])) {
            $validator->isString($var['delivery_city'], 'Leveringsadressens by', '', 'allow_empty');
        }
        if (!empty($var['delivery_country'])) {
            $validator->isString($var['delivery_country'], 'Leveringsadressens land', '', 'allow_empty');
        }

        // other
        if (!empty($var['paymentcondition'])) {
            $var['paymentcondition'] = 0;
        }
        settype($var['paymentcondition'], 'integer');
        $validator->isNumeric($var['paymentcondition'], 'Betalingsbetingelserne er ikke sat', 'allow_empty');
        if (empty($var['paymentcondition'])) {
            //$var['paymentcondition'] = $this->kernel->setting->get('intranet', 'contact.standard_paymentcondition');
            $var['paymentcondition'] = 8;
        }

        if (empty($var['preferred_invoice'])) {
            $var['preferred_invoice'] = 0;
        }
        settype($var['preferred_invoice'], 'integer');
        $validator->isNumeric($var['preferred_invoice'], 'Fejl i preferred_invoice', 'allow_empty');
        /*
         if ($var['preferred_invoice'] == 3 AND empty($var['ean'])) {
         // @todo this creates problems from the shop when EAN has been chosen
         $this->error->set('Du skal udfylde EAN-nummeret, hvis du v�lger en elektronisk faktura');
         }
         */

        if ($var['preferred_invoice'] == 2 AND empty($var['email'])) {
            $this->error->set('E-mailen skal udfyldes, hvis kontakten foretr�kker e-mail.');
        }

        if ($this->error->isError()) {
            //$this->error->view();
            return false;
        }
        return true;
    }


    /**
     * Saves the contact
     *
     * @param	int $var['id']	Kundeid
     * @param	string $var['company']
     * @param	string $var['address']
     * @param	string $var['postalcode']
     * @param	string $var['town']
     * @param	string $var['country']
     * @param	string $var['email']
     * @param	string $var['website']
     * @param	string $var['phone']
     * @param	string $var['deliveryaddress']
     * @param	string $var['deliverypostalcode']
     * @param	string $var['deliverytown']
     * @param	string $var['deliverycountry']
     * @param	string $var['paymentcondition']
     *
     * @return void
     */
    public function save($var)
    {
        $sql_items = '';
        // safe db m� ikke v�re her, for den k�res igen i address
        // vi skal s�reg for blot at k�re den p� selve feltet.
        //$var = safeToDb($var);

        if ($this->id == 0 AND empty($var['number'])) {
            $var['number'] = $this->getMaxNumber() + 1;
        }

        if (isset($var['type'])) {
            $type_key = array_search($var['type'], $this->types);
            if ($type_key === false) {
                $this->error->set('invalid type for the contact');
            } else {
                $var['type_key'] = $type_key;
            }
        }

        if (!$this->validate($var)) {
            return 0;
        }

        // skrive sql
        foreach ($this->fields as $field) {
            if (!array_key_exists($field, $var)) {
                continue;
            }
            if (isset($var[$field])) {
                $sql_items .= $field." = '".safeToDb($var[$field])."', ";
            }
        }


        // prepare sql to update or insert
        if ($this->id > 0) {
            $sql="UPDATE contact ";
            $sql_after=" WHERE id='".$this->id."'";
            $sql_create = "";
        } else {
            $sql="INSERT INTO contact ";
            $sql_after = ", code='".safeToDb(md5(date('Y-m-d H:i:s') . $sql_items))."'";
            $sql_create = "date_created = NOW(),";
        }

        $sql .= " SET	".$sql_create. "
            intranet_id = '". $this->kernel->intranet->get('id')."',"
            .$sql_items.
            "date_changed = NOW()
            $sql_after";

            $db = new DB_Sql;
            $db->query($sql);
            if ($this->id == 0) {
                $this->id = $db->insertedId();
            }

            // Standardadresse
            $address_object = Intraface_Address::factory('contact', $this->id);
            $address_fields = $address_object->fields;

            foreach ($address_fields AS $key=>$value) {
                if (array_key_exists($value, $var)) {
                    $standard_address_to_save[$value] = $var[$value];
                }
            }
            if (!empty($standard_address_to_save)) {
                if (!$address_object->save($standard_address_to_save)) {
                    return 0;
                }
            }

            // Delivery Address
            foreach ($address_fields AS $key=>$value) {
                if (array_key_exists('delivery_'.$value, $var)) {
                    $delivery_address_to_save[$value] = $var['delivery_' . $value];
                }
            }

            $delivery_address_object = Intraface_Address::factory('contact_delivery', $this->id);

            if (!empty($delivery_address_to_save)) {
                if (!$delivery_address_object->save($delivery_address_to_save)) {
                    return 0;
                }
            }
            $this->load();

            return $this->id;
    }

    /**
     * Deletes a contact
     *
     * Never delete a contact entirely. Should only be deactivated.
     *
     * @return integer	0 = false eller 1 = true
     */
    public function delete()
    {
        if ($this->get('locked') == 1) {
            $this->error->set('Posten er l�st og kan ikke slettes');
            return false;
        }
        if ($this->id == 0) {
            $this->error->set('Kender ikke id, s� kan ikke slette kunden');
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET active = 0, date_changed = NOW() WHERE intranet_id = " . $this->kernel->intranet->get("id") . " AND id = " . $this->id);
        return true;
    }

    /**
     * Undelete
     *
     * @return boolean
     */
    public function undelete()
    {
        if ($this->get('locked') == 1) {
            $this->error->set('Posten er l�st og kan ikke slettes');
            return false;
        }
        if ($this->id == 0) {
            $this->error->set('Kender ikke id, s� kan ikke slette kunden');
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET active = 1, date_changed = NOW() WHERE intranet_id = " . $this->kernel->intranet->get("id") . " AND id = " . $this->id);

        return true;
    }

    /**
     * Tjekke om kundenummeret er frit
     *
     * @param string $number
     *
     * @return true hvis det er frit
     */
    public function isNumberFree($number)
    {
        $number = (int)$number;
        $db = new DB_Sql();
        $sql = "SELECT id
            FROM contact
            WHERE intranet_id = " . $this->kernel->intranet->get("id") . "
                AND number = " . (int)$number . "
                AND id <> " . $this->id . "
                AND active = 1
            LIMIT 1";
        $db->query($sql);
        if ($db->numRows() == 0) {
            return true;
        }
        return false;
    }

    /**
     * Hente det maksimale kundenummer
     *
     * @return integer
     */
    public function getMaxNumber()
    {
        $db = new DB_Sql();
        $db->query("SELECT number FROM contact WHERE intranet_id = " . $this->kernel->intranet->get("id") . " ORDER BY number DESC");
        if (!$db->nextRecord()) {
            return 0;
        }
        return $db->f("number");
    }

    /**
     * Public: Finde data til en liste
     *
     * @param string $parameter hvad er det?
     *
     * @return array indeholdende kundedata til liste
     */
    public function getList($parameter = "")
    {
        if ($this->getDBQuery()->checkFilter("search")) {
            $search = $this->getDBQuery()->getFilter("search");
            $this->getDBQuery()->setCondition("
                contact.number = '".$search."' OR
                address.name LIKE '%".$search."%' OR
                address.address LIKE '%".$search."%' OR
                address.email LIKE '%".$search."%' OR
                address.phone = '".$search."'");
        }

        $this->getDBQuery()->setSorting("address.name");

        $i = 0; // til at give arrayet en key

        $db = $this->getDBQuery()->getRecordset("contact.id, contact.number, contact.paymentcondition, address.name, address.email, address.phone, address.address, address.postcode, address.city", "", false);

        $contacts = array();
        while ($db->nextRecord()) {
            //
            $contacts[$i]['id'] = $db->f("id");
            $contacts[$i]['number'] = $db->f("number");
            $contacts[$i]['paymentcondition'] = $db->f("paymentcondition");
            $contacts[$i]['name'] = $db->f("name");
            $contacts[$i]['address'] = $db->f("address");
            $contacts[$i]['postcode'] = $db->f("postcode");
            $contacts[$i]['city'] = $db->f("city");
            $contacts[$i]['phone'] = $db->f("phone");
            $contacts[$i]['email'] = $db->f("email");

            if ($parameter == "use_address") {
                $address = Intraface_Address::factory("contact", $db->f("id"));
                $contacts[$i]['address'] = $address->get();
            }

            $i++;
        }
        return $contacts;
    }

    /**
     * has contact any similar contacts based on phonenumber and email
     *
     * @return boolean
     */
    public function hasSimilarContacts()
    {
        $contacts = $this->getSimilarContacts();
        return (count($contacts) > 0);
    }

    /**
     * Return an array with similar contacts
     *
     * @return array
     */
    public function getSimilarContacts()
    {
        $this->address = $this->getAddress();

        $similar_contacts = array();

        if ($this->id == 0) {
            return array();
        }

        $this->load();

        $db = MDB2::singleton(DB_DSN);
        $sql = "SELECT DISTINCT(contact.id), contact.number, address.name, address.id AS address_id, address.address, address.postcode, address.phone, address.email, address.city FROM contact
                INNER JOIN address
                    ON contact.id = address.belong_to_id
                WHERE address.type=3
                    AND contact.active = 1
                    AND address.active = 1
                    AND contact.intranet_id = " . $db->quote($this->kernel->intranet->get('id'), 'integer') . "
                    AND contact.id != " . $db->quote($this->id, 'integer') . "
                    AND (
                        (address.email = " . $db->quote($this->address->get('email'), 'text') . " AND address.email != '')
                        OR (address.phone = " . $db->quote($this->address->get('phone'), 'text') . " AND address.phone != ''))

                    ";

        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage().' '.$result->getUserInfo(), E_USER_ERROR);
        }

        if ($result->numRows() == 0) {
            return array();
        }

        return $result->fetchAll();
    }

    /**
     * Merges a contact to one contact
     *
     * - debtor
     * - newsletter
     * - procurement
     * - intranet
     *
     * These needs to implement a common function changeContact($old, $new)
     */
    function merge()
    {
        die('Contact::merge(): Ikke implementeret');
    }

    /**
     * Start keywordmodulet op
     * Denne metode kr�ves af keyword
     *
     * TODO M�ske burde denne metode hedde loadKeywords()?
     *
     * @return object
     */
    function getKeywords()
    {
        return $this->keywords = new Keyword($this);
    }

    function getKeyword()
    {
        return $this->getKeywords();
    }

    function getKeywordAppender()
    {
        return new Intraface_Keyword_Appender($this);
    }

    /**
     * Start message op
     *
     * @deprecated
     *
     * @param integer $id Optional id for the message
     *
     * @return object
     */
    private function loadMessage($id = 0)
    {
        return $this->message = new ContactMessage($this, (int)$id);
    }

    /**
     * Hvis kontakten er et firma, skal den loade og inkludere kontaktpersonerne
     *
     * @param integer $id Optional id of the contact person
     *
     * @return object
     */
    function loadContactPerson($id = 0)
    {
        return ($this->contactperson = new ContactPerson($this, (int)$id));
    }

    /**
     * Funktionen skal tjekke om der er tastet nogen kontaktpersoner ind overhovedet.
     * Funktionen er tilt�nkt et tjek, s� man hurtigt kan tjekke om brugeren har nogen.
     *
     * @return integer
     */
    function isFilledIn()
    {
        $db = new DB_Sql;
        $db->query("SELECT count(*) AS antal FROM contact WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        if ($db->nextRecord()) {
            return $db->f('antal');
        }
        return 0;
    }

    /**
     * Generates a password for the contact
     *
     * @return boolean
     */
    function generateCode()
    {
        if ($this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET code = '".md5($this->id . date('Y-m-d H:i:s') . $this->kernel->intranet->get('id'))."' WHERE id = " . $this->id);
        $this->load();
        return true;
    }


    /**
     * Generates a password for the contact
     *
     * @return boolean
     */
    function generatePassword()
    {
        if ($this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET password = '".md5($this->id . date('Y-m-d H:i:s') . $this->kernel->intranet->get('id'))."' WHERE id = " . $this->id);
        $this->load();
        return true;
    }


    /**
     * Sends the login email for the contact
     *
     * @param object mailer
     * @return boolean
     */
    /*
    function sendLoginEmail($mailer)
    {
        if (!is_object($mailer)) {
            throw new Exception('A valid mailer object must be provided');
        }

        if ($this->id == 0) {
            $this->error->set('Der er ikke noget id, s� kunne ikke sende en e-mail');
            return false;
        }
        // opretter en kode, hvis kunden ikke har en kode
        if (!$this->get('password')) {
            $db = new DB_Sql;
            $db->query("UPDATE contact SET password = '".md5($this->get('id') . date('Y-m-d H:i:s'))."' WHERE id = " . $this->id . " AND intranet_id=" . $this->kernel->intranet->get('id'));
        }

        $this->load();

        $this->kernel->useShared('email');
        $email = new Email($this->kernel);
        if (!$email->save(
        array(
                'subject' => 'Loginoplysninger',
                'body' => $this->kernel->setting->get('intranet', 'contact.login_email_text') . "\n\n" . $this->getLoginUrl() . "\n\nMed venlig hilsen\nEn venlig e-mail-robot\n" . $this->kernel->intranet->get('name'),
                'contact_id' => $this->id,
                'from_email' => $this->kernel->intranet->address->get('email'),
                'from_name' => $this->kernel->intranet->get('name'),
                'type_id' => 9,
                'belong_to' => $this->get('id')
        )
        )) {
            $this->error->set('Kunne ikke gemme emailen');
            return false;
        }

        if ($email->send($mailer)) {
            $this->error->set('E-mailen er sendt');
            return true;
        }
        $this->error->set('Kunne ikke sende emailen');
        return false;
    }
    */

    /**
     * Gets the contacts newsletter subscriptions
     *
     * @return array
     */
    function getNewsletterSubscriptions()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE optin = 1 AND active = 1 AND contact_id = " . $this->id . " AND intranet_id =" . $this->kernel->intranet->get('id'));
        $lists = array();
        while ($db->nextRecord()) {
            $lists[] = $db->f('list_id');
        }
        return $lists;
    }

    /**
     * Checks whether the contact needs to accept subscriptions
     *
     * @return array
     */
    function needNewsletterOptin()
    {
        $db = new DB_Sql;
        $db->query("SELECT list_id, code FROM newsletter_subscriber WHERE optin = 0 AND contact_id = " . $this->id . " AND intranet_id =" . $this->kernel->intranet->get('id'));
        $lists = array();
        $i = 0;
        while ($db->nextRecord()) {
            $lists[$i]['list_id'] = $db->f('list_id');
            $lists[$i]['code'] = $db->f('code');
            $i++;
        }
        return $lists;
    }

    /**
     * Kontakten kan slettes, hvis man kun er indskrevet i nyhedsbrevet.
     * Der b�r sikkert ogs� v�re en indstilling som ejeren af intranettet kan s�tte
     * efter al sandsynlighed skal denne v�re med som tjek i delete
     *
     * @return boolean
     */
    function canBeDeleted()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM debtor WHERE contact_id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return true;
        }
        return false;
    }

    /**
     * skal tage h�jde for om intranettet tillader kundelogin
     *
     * @return boolean
     */
    function canLogin()
    {
        if ($this->get('active') == 0) {
            return false;
        }
        return true;
    }

    function getId()
    {
        return $this->id;
    }

    function getType()
    {
        return $this->types[$this->value['type_key']];
    }
}