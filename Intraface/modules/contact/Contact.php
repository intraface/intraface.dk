<?php
/**
 * Kontakthåndteringsklasse
 *
 * Klassen håndterer en kontaktperson, eller kan returnere en liste med kontaktperson.
 *
 * TODO  Vi skal have lavet det så man kan slette felter igen.
 *       Den er oprindeligt lavet så det kun er satte felter, der ændres,
 *       men den skal egentlig ændre felter, hvor brugeren har haft et felt,
 *       som de kunne ændre værdien med. Gad vide om man kan det?
 *
 *
 * @package Contact
 * @author	Lars Olesen <lars@legestue.net>
 * @author	Sune Jensen <sj@sunet.dk>
 * @version	1.0
 * @since	1.0
 * @access Public
 * @copyright 	Lars Olesen
 * @license
 *
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/Validator.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Address.php';

class Contact extends Standard {

    /**
     * Brugerobjekt
     * @var object
     * @access private
     */
    var $kernel;

    /**
     * Kundeid
     * @var integer
     * @access public
     */
    var $id;

    /**
     * Værdier for kunden
     * @var array
     * @access public
     */
    var $value;

    /**
     * Adresseobjekt
     * @var object
     * @access public
     */
    var $address;

    /**
     * Leveringsaddresse
     * @var object
     * @access public
     */
    var $delivery_address;

    /**
     * Errorobjekt
     * @var object
     * @access public
     */
    var $error;

    var $contactperson;
    var $message;
    var $keywords;
    var $lock;

    var $addresses = array(
        0 => 'standard',
        1 => 'delivery',
        2 => 'invoice'
    );

    /**
     * Mulige typer 0: private 1: corporate
     */
    var $types = array(
        0 => 'private',
        1 => 'corporation'
    );

    /**
     * Init
     *
     * @param	object	$kernel
     * @param	int	$id	Kundeid.
     * @return	void
     * @access private
     */
    function __construct($kernel, $id = 0) {
        if (!is_object($kernel)) {
            trigger_error('Contact kræver kernel - fik ' . get_class($kernel), E_USER_ERROR);
        }
        $this->kernel = $kernel;
        //$contact_module = $this->kernel->getModule('contact');
        //$this->types = $contact_module->getSetting('type');

        $this->error = new Error;
        $this->id = (int)$id;

        $this->fields = array('type_key', 'paymentcondition', 'number', 'preferred_invoice', 'openid_url');

        if($this->id > 0) {
            $this->load();
        }
    }

    function createDBQuery() {
        $this->dbquery = new DBQuery($this->kernel, "contact", "contact.active = 1 AND contact.intranet_id = ".$this->kernel->intranet->get("id"));
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id", "address.active = 1 AND address.type = 3");
        $this->dbquery->useErrorObject($this->error);
    }

    function factory($kernel, $type, $value) {
        // Husk noget validering af de forskellige values og typer
        $db = new DB_Sql;
        switch($type) {
            case 'email':
                $db->query("SELECT address.belong_to_id AS id FROM contact INNER JOIN address ON address.belong_to_id = contact.id WHERE address.email = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id') . " AND address.active = 1 AND contact.active = 1");
                break;
            case 'code':
                $db->query("SELECT id FROM contact WHERE password  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
                break;
            case 'openid_url':
                $db->query("SELECT id FROM contact WHERE openid_url  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
                // Her bør vel være et tjek på hvor mange - og hvis mange give en fejl
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
    }

    /**
     * Loader værdierne ind i et array
     *
     * @return true on success
     */

    function load() {
        $db = new DB_Sql;
        $this->value = array();

        $db->query("SELECT id, ".implode(',', $this->fields).", password
            FROM contact
            WHERE contact.id=".$this->id."
                AND intranet_id =".$this->kernel->intranet->get('id'));

        if(!$db->nextRecord()) {
            return 0;
        }

        $this->value['id'] = $db->f('id');

        for($i=0, $max=count($this->fields); $i<$max; $i++) {
            $this->value[$this->fields[$i]] = $db->f($this->fields[$i]);
        }
        $this->value['type'] = $this->types[$db->f('type_key')];
        $this->value['type_key'] = $db->f('type_key');
        $this->value['password'] = $db->f('password');
        $this->value['number'] = $db->f('number');
        $this->value['code'] = $db->f('password');

        if($this->get('type') == 'corporation') {
            $this->contactperson = new ContactPerson($this);
        }

        $this->address = Address::factory('contact', $db->f('id'));
        $this->delivery_address = Address::factory('contact_delivery', $db->f('id'));

        // name må ikke fjernes - bruges af keywords
        $this->value['name'] = $this->address->get('name');
        $this->value['openid_url'] = $this->get('openid_url');

        $this->value['id'] = $db->f('id'); // må ikke fjernes

        return 1;
    }

    function getLoginUrl() {
        // HACK NECCESSARY FOR THE NEWSLETTERSUBSCRIBER
        $this->kernel->useModule('contact');
        return $this->value['login_url'] = 'http://' . $this->kernel->intranet->get('identifier') . '.' . $this->kernel->setting->get('intranet', 'contact.login_url') . '/' . $this->get('password');

    }

    function validate($var) {
        $var = $var;

        if (array_key_exists('number', $var) AND !$this->isNumberFree($var['number'])) {
            $this->error->set('Kundenummeret er ikke frit');
        }

        $validator = new Validator($this->error);
        if (!empty($var['type_key'])) $validator->isNumeric($var['type_key'], 'Fejl i typen', 'allow_empty');
        if (!empty($var['openid_url'])) $validator->isUrl($var['openid_url'], 'Openid_url', 'allow_empty');

        // address
        $validator->isString($var['name'], 'Navnet er ikke en gyldig streng');
        if (!empty($var['address'])) $validator->isString($var['address'], 'Adressen er ikke en streng', '', 'allow_empty');
        if (!empty($var['postcode'])) $validator->isNumeric($var['postcode'], 'Postkoden er ikke et tal', 'allow_empty');
        if (!empty($var['city'])) $validator->isString($var['city'], 'Byen er ikke en by', '', 'allow_empty');
        if (!empty($var['country'])) $validator->isString($var['country'], 'Der er fejl i landet', '', 'allow_empty');
        if (!empty($var['phone'])) $validator->isString($var['phone'], 'Telefonnummeret et ikke rigtigt.', '', 'allow_empty');
        if (!empty($var['email'])) $validator->isEmail($var['email'], 'E-mailen er ikke en rigtig e-mail', 'allow_empty');
        if (!empty($var['website'])) $validator->isUrl($var['website'], 'Der er fejl i urlen', 'allow_empty');
        if (!empty($var['cvr'])) $validator->isNumeric($var['cvr'], 'Fejl i cvr-nummeret', 'allow_empty');
        settype($var['ean'], 'string');
        if (!empty($var['ean'])) $validator->isNumeric($var['ean'], 'Fejl i ean-nummeret', 'allow_empty');

        if (!empty($var['ean']) AND strlen($var['ean']) != 13) {
            $this->error->set('EAN-nummeret skal præcis være 13 tal');
        }

        //deliveryaddress
        if (!empty($var['delivery_name'])) $validator->isString($var['delivery_name'], 'Leveringsadressen forkert', '', 'allow_empty');
        if (!empty($var['delivery_address'])) $validator->isString($var['delivery_address'], 'Leveringsadressen forkert', '', 'allow_empty');
        if (!empty($var['delivery_postcode'])) $validator->isNumeric($var['delivery_postcode'], 'Leveringsadressens postnummer', 'allow_empty');
        if (!empty($var['delivery_city'])) $validator->isString($var['delivery_city'], 'Leveringsadressens by', '', 'allow_empty');
        if (!empty($var['delivery_country'])) $validator->isString($var['delivery_country'], 'Leveringsadressens land', '', 'allow_empty');

        // other
        if (!empty($var['paymentcondition'])) $var['paymentcondition'] = 0;
        settype($var['paymentcondition'], 'integer');
        $validator->isNumeric($var['paymentcondition'], 'Betalingsbetingelserne er ikke sat', 'allow_empty');
        if (empty($var['paymentcondition'])) {
            //$var['paymentcondition'] = $this->kernel->setting->get('intranet', 'contact.standard_paymentcondition');
            $var['paymentcondition'] = 8;
        }

        if (empty($var['preferred_invoice'])) $var['preferred_invoice'] = 0;
        settype($var['preferred_invoice'], 'integer');
        $validator->isNumeric($var['preferred_invoice'], 'Fejl i preferred_invoice', 'allow_empty');
        if ($var['preferred_invoice'] == 3 AND empty($var['ean'])) {
            $this->error->set('Du skal udfylde EAN-nummeret, hvis du vælger en elektronisk faktura');
        }

        if ($var['preferred_invoice'] == 2 AND empty($var['email'])) {
            $this->error->set('E-mailen skal udfyldes, hvis kontakten foretrækker e-mail.');
        }


        if ($this->error->isError()) {
            //$this->error->view();
            return 0;
        }
        return 1;
    }


    /**
     * Public: Opdatere kunden
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
     * @return void
      * @access public
     */

    function save($var) {
        $sql_items = '';

        // safe db må ikke være her, for den køres igen i address
        // vi skal søreg for blot at køre den på selve feltet.
        //$var = safeToDb($var);

        if ($this->id == 0 AND empty($var['number'])) {
            $var['number'] = $this->getMaxNumber() + 1;
        }

        if (!$this->validate($var)) {
            return 0;
        }

        // skrive sql
        for($i = 0, $max = count($this->fields); $i<$max; $i++) {
            if(!array_key_exists($this->fields[$i], $var)) continue;
            if(isset($var[$this->fields[$i]])) {
                $sql_items .= $this->fields[$i]." = '".safeToDb($var[$this->fields[$i]])."', ";
            }
        }


        // prepare sql to update or insert
        if($this->id > 0) {
            $sql="UPDATE contact ";
            $sql_after=" WHERE id='".$this->id."'";
            $sql_create = "";
        }
        else {
            $sql="INSERT INTO contact ";
            $sql_after = ", password='".safeToDb(md5(date('Y-m-d H:i:s') . $sql_items))."'";
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

        #
        # Standardadresse
        #

        $address_object = Address::factory('contact', $this->id);
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
        #
        # Delivery Address
        #

        foreach ($address_fields AS $key=>$value) {
            if (array_key_exists('delivery_'.$value, $var)) {
                $delivery_address_to_save[$value] = $var['delivery_' . $value];
            }
        }

        $delivery_address_object = Address::factory('contact_delivery', $this->id);


        if (!empty($delivery_address_to_save)) {
            if (!$delivery_address_object->save($delivery_address_to_save)) {
                return 0;
            }
        }
        $this->load();

        return $this->id;
    }

    /**
     * Public: Slette en kunde
     *
     * Klassen må aldrig slette kunden helt, men må bare gøre kunden inaktiv
     *
     * @return integer	0 = false eller 1 = true
     * @access public
     */

    function delete() {
        if ($this->get('locked') == 1) {
            $this->error->set('Posten er låst og kan ikke slettes');
            return 0;
        }
        if ($this->id == 0) {
            $this->error->set('Kender ikke id, så kan ikke slette kunden');
            return 0;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET active = 0, date_changed = NOW() WHERE intranet_id = " . $this->kernel->intranet->get("id") . " AND id = " . $this->id);
        return 1;
    }

    /**
     * Funktionen bruges at fortryde en sletning.
     */

    function undelete() {
        if ($this->get('locked') == 1) {
            $this->error->set('Posten er låst og kan ikke slettes');
            return 0;
        }
        if ($this->id == 0) {
            $this->error->set('Kender ikke id, så kan ikke slette kunden');
            return 0;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET active = 1, date_changed = NOW() WHERE intranet_id = " . $this->kernel->intranet->get("id") . " AND id = " . $this->id);

        return 1;
    }







    /**
     * Tjekke om kundenummeret er frit
     * @return true hvis det er frit
     * @access public
     */

    function isNumberFree($number) {
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
     * @return integer
     * @access public
     */

    function getMaxNumber() {
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
     *
     * @param	$parameter hvad er det?
     * @return array indeholdende kundedata til liste
     * @access public
     */
    function getList($parameter = "") {

        if($this->dbquery->checkFilter("search")) {
            $search = $this->dbquery->getFilter("search");
            $this->dbquery->setCondition("
                contact.number = '".$search."' OR
                address.name LIKE '%".$search."%' OR
                address.address LIKE '%".$search."%' OR
                address.email LIKE '%".$search."%' OR
                address.phone = '".$search."'");
        }

        $this->dbquery->setSorting("address.name");

        $i = 0; // til at give arrayet en key

        $db = $this->dbquery->getRecordset("contact.id, contact.number, contact.paymentcondition, address.name, address.email, address.phone, address.address, address.postcode, address.city", "", false);

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

            if($parameter == "use_address") {
                $address = Address::factory("contact", $db->f("id"));
                $contacts[$i]['address'] = $address->get();
            }

            $i++;
        }
        return $contacts;
    }

    /**
     * Metoden skal bruges til at sammenligne kunder mhp. på at samle dem til en kunde.
     *
     * @see Contact::merge();
     */

    function compare() {
        $similar_contacts = array();

        if ($this->id == 0) {
            return array();
        }

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

    /*
    function compare() {
        $similar_contacts = array();

        if ($this->id == 0) {
            return array();
        }

        $db = MDB2::singleton(DB_DSN);
        $sql = "SELECT DISTINCT(contact.id) FROM contact
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
        print($sql);
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            trigger_error($result->getMessage(), E_USER_ERROR);
        }

        if ($result->numRows() == 0) {
            return array();
        }

        print_r($result->fetchAll());


        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $contact = new Contact($this->kernel, $row['id']);
            $similar_contacts[] = $contact;
        }

        return $similar_contacts;
  }
    */


    /**
     * Denne metode skal bruges til at samle to kontaktpersoner til en.
     *
     * - debtor
     * - newsletter
     * - procurement
     * - intranet
     */
    function merge() {
        die('Contact::merge(): Ikke implementeret');
    }

    /**
     * Start keywordmodulet op
     * Denne metode kræves af keyword
     *
     * TODO Måske burde denne metode hedde loadKeywords()?
     */
    function getKeywords() {
        return $this->keywords = new Keyword($this);
    }

  /**
   * Start message op
    */
    function loadMessage($id = 0) {
        $this->message = & new ContactMessage($this, (int)$id);
    }

    /**
     * Hvis kontakten er et firma, skal den loade og inkludere kontaktpersonerne
     */
    function loadContactPerson($id = 0) {
        return ($this->contactperson = & new ContactPerson($this, (int)$id));
    }

    /**
     * Funktionen skal tjekke om der er tastet nogen kontaktpersoner ind overhovedet.
     * Funktionen er tiltænkt et tjek, så man hurtigt kan tjekke om brugeren har nogen.
     */
    function isFilledIn() {
        $db = new DB_Sql;
        $db->query("SELECT count(*) AS antal FROM contact WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        if ($db->nextRecord()) {
            return $db->f('antal');
        }
        return 0;
    }

    function generatePassword() {
        if ($this->id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("UPDATE contact SET password = '".md5($this->id . date('Y-m-d H:i:s') . $this->kernel->intranet->get('id'))."' WHERE id = " . $this->id);
        return 1;
    }

    function sendLoginEmail() {

        if ($this->id == 0) {
            $this->error->set('Der er ikke noget id, så kunne ikke sende en e-mail');
            return 0;
        }
        // opretter en kode, hvis kunden ikke har en kode
        if (!$this->get('password')) {
            $db = new DB_Sql;
            $db->query("UPDATE contact SET password = '".md5($this->get('id') . date('Y-m-d H:i:s'))."' WHERE id = " . $this->id . " AND intranet_id=" . $this->kernel->intranet->get('id'));
        }

        $this->load();


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
            return 0;
        }

        if ($email->send()) {
            $this->error->set('E-mailen er sendt');
            return 1;
        }
        $this->error->set('Kunne ikke sende emailen');
        return 0;
    }

    function getNewsletterSubscriptions() {
        $db = new DB_Sql;
        $db->query("SELECT * FROM newsletter_subscriber WHERE optin = 1 AND active = 1 AND contact_id = " . $this->id . " AND intranet_id =" . $this->kernel->intranet->get('id'));
        $lists = array();
        while ($db->nextRecord()) {
            $lists[] = $db->f('list_id');
        }
        return $lists;
    }

    function needNewsletterOptin() {
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
     * Der bør sikkert også være en indstilling som ejeren af intranettet kan sætte
     * efter al sandsynlighed skal denne være med som tjek i delete
     */

    function canBeDeleted() {
        $db = new DB_Sql;
        $db->query("SELECT * FROM debtor WHERE contact_id = " . $this->id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return 1;
        }
        return 0;
    }

    /**
     * skal tage højde for om intranettet tillader kundelogin
     */

    function canLogin() {
        if ($this->get('active') == 0) {
            return 0;
        }
    }

}
?>