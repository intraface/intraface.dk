<?php
class IntranetNews extends Intraface_Standard
{
    var $kernel;
    var $id;
    var $values;
    var $error;

    function __construct($kernel, $id = 0)
    {
        $this->kernel = &$kernel;
        $this->id = intval($id);
        $this->error = new Intraface_Error;

        if($this->id != 0) {
            $this->load();
        }
    }

    function load()
    {
        $db = new DB_sql;

        $db->query("SELECT id, area, description FROM systemmessage_news WHERE active = 1 AND id = ".$this->id);
        if($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['area'] = $db->f('area');
            $this->value['description'] = $db->f('description');
        } else {
            $this->id = 0;
        }
    }

    function update($input)
    {
        $input = safeToDb($input);

        $validator = new Validator($this->error);

        $validator->isString($input['area'], 'Område er ikke udfylde korrekt', '', 'allow_empty');
        $validator->isString($input['description'], 'Beskrivelsen er ikke udfyldt korrekt', '<strong>');

        if($this->error->isError()) {
            return 0;
        }

        $sql = "user_name = \"".$this->kernel->user->getAddress()->get('name')."\",
            area = \"".$input['area']."\",
            description = \"".$input['description']."\",
            active = 1";

        $db = new DB_sql;

        if($this->id != 0) {
            $db->query("UPDATE systemmessage_news SET ".$sql." WHERE id = ".$this->id);
        } else {
            $db->query("INSERT INTO systemmessage_news SET date_created = NOW(), ".$sql);
            $this->id = $db->insertedId();
        }

        return $this->id;

    }

    function delete()
    {
        if($this->id == 0) {
            return 0;
        }

        $db = new DB_sql;

        $db->query("UPDATE systemmessage_news SET active = 0 WHERE id = ".$this->id);

        return 1;
    }

    function getList($to_date = "")
    {
        $db = new DB_sql;

        if($to_date == "") {
            $to_date = "0000-00-00";
        }
        $value = array();
        $i = 0;

        $db->query("SELECT id, user_name, area, description, DATE_FORMAT(date_created, '%d-%m-%Y %H:%i') AS dk_date_time FROM systemmessage_news WHERE active = 1 AND date_created > \"".$to_date."\" ORDER BY date_created DESC");
        while($db->nextRecord()) {

            $value[$i]['id'] = $db->f('id');
            $value[$i]['dk_date_time'] = $db->f('dk_date_time');
            $value[$i]['user_name'] = $db->f('user_name');
            $value[$i]['area'] = $db->f('area');
            $value[$i]['description'] = $db->f('description');

            $i++;
        }

        return $value;
    }
}