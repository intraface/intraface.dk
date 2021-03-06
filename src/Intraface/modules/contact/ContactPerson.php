<?php
/**
 * @package Intraface_Contact
 */
class ContactPerson extends Intraface_Standard
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var object
     */
    private $contact;

    /**
     * @var array
     */
    public $value;

    /**
     * @var object
     */
    public $error;

    /**
     * Constructor
     *
     * @param object  $contact
     * @param integer $id
     *
     * @return void
     */
    public function __construct($contact, $id = 0)
    {
        if (!is_object($contact) or strtolower(get_class($contact)) != 'contact') {
            throw new Exception('ContactPerson kr�ver Contact som object');
        }
        $this->contact = $contact;
        $this->id = (int)$id;

        $this->error = $this->contact->error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getError()
    {
        return $this->error;
    }


    /**
     * Loads the contact person
     *
     * @return void
     */
    public function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name, email, phone, mobile, contact_id FROM contact_person WHERE id = " . $this->id . " LIMIT 1");
        while ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['name'] = $db->f('name');
            $this->value['email'] = $db->f('email');
            $this->value['phone'] = $db->f('phone');
            $this->value['mobile'] = $db->f('mobile');
            $this->value['contact_id'] = $db->f('contact_id');
        }
    }

    /**
     * Saves the contact person
     *
     * @param array $input
     *
     * @return integer
     */
    public function save($input)
    {
        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);
        $validator->isString($input['name'], 'Fejl i kontaktpersonens navn', '', 'allow_empty');

        settype($input['email'], 'string');
        $validator->isEmail($input['email'], 'Fejl i kontaktpersonens e-mail', 'allow_empty');
        settype($input['phone'], 'string');
        $validator->isString($input['phone'], 'Fejl i kontaktpersonens telefon', '', 'allow_empty');
        settype($input['mobile'], 'string');
        $validator->isString($input['mobile'], 'Fejl i kontaktpersonens mobil', '', 'allow_empty');

        if ($this->error->isError()) {
            return false;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        }


        $db = MDB2::singleton(DB_DSN);

        $result = $db->exec($sql_type . "contact_person " .
                "SET " .
                "intranet_id = ".$db->quote($this->contact->kernel->intranet->get("id"), 'integer').", " .
                "name = ".$db->quote($input['name'], 'text').", " .
                "email = ".$db->quote($input['email'], 'text').", " .
                "phone = ".$db->quote($input['phone'], 'text').", " .
                "mobile = ".$db->quote($input['mobile']).", " .
                "contact_id = " . $db->quote($this->contact->get('id'), 'integer') . ", " .
                "date_changed = NOW() " . $sql_end);

        if (PEAR::isError($result)) {
            throw new Exception('Error in query: '.$result->getUserInfo());
            exit;
        }

        if ($this->id == 0) {
            $id = $db->lastInsertID('contact_person', 'id');
            if (PEAR::isError($id)) {
                throw new Exception('Error in query: '.$id->getUserInfo());
                exit;
            }
            $this->id = $id;
        }

        return $this->id;
    }

    /**
     * Gets list with all contact persons
     *
     * @return array
     */
    public function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM contact_person WHERE contact_id = " . $this->contact->get('id'));
        $persons = array();
        $i = 0;
        while ($db->nextRecord()) {
            $persons[$i]['id'] = $db->f('id');
            $persons[$i]['name'] = $db->f('name');
            $persons[$i]['email'] = $db->f('email');
            $persons[$i]['mobile'] = $db->f('mobile');
            $persons[$i]['phone'] = $db->f('phone');
            $i++;
        }
        return $persons;
    }
}
