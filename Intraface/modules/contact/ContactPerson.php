<?php
/**
 * @package Intraface_Contact
 */

class ContactPerson extends Standard {

    var $id;
    var $contact;
    var $value;
    var $error;

    function ContactPerson(& $contact, $id = 0) {
        if (!is_object($contact) OR strtolower(get_class($contact)) != 'contact') {
            trigger_error('ContactPerson kræver Contact som object');
        }
        $this->contact = & $contact;
        $this->id = (int)$id;

        $this->error = $this->contact->error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    function load() {
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

    function save($input) {
        $input = safeToDb($input);

        $validator = new Validator($this->error);
        $validator->isString($input['name'], "Fejl i navn");

        settype($input['email'], 'string');
        $validator->isEmail($input['email'], "Fejl i e-mail", 'allow_empty');
        settype($input['phone'], 'string');
        $validator->isString($input['phone'], 'Fejl i telefon', '', 'allow_empty');
        settype($input['mobile'], 'string');
        $validator->isString($input['mobile'], 'Fejl i mobil', '', 'allow_empty');

        if ($this->error->isError()) {
            return 0;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }
        else {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        }

        $db = new DB_Sql;
        $db->query($sql_type . "contact_person SET intranet_id = ".$this->contact->kernel->intranet->get("id").", name = '".safeTodb($input['name'])."', email = '".safeToDb($input['email'])."',phone = '".safeToDb($input['phone'])."',mobile = '".safeToDb($input['mobile'])."', contact_id = " . intval($this->contact->get('id')) . ", date_changed = NOW() " . $sql_end);

        if ($this->id == 0) {
         $this->id = $db->insertedId();
        }

        return $this->id;
    }

    function getList() {
        $db = new DB_Sql;
        $db->query("SELECT * FROM contact_person WHERE contact_id = " . $this->contact->id);
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

?>