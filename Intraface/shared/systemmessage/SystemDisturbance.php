<?php
class SystemDisturbance extends Intraface_Standard
{
    var $kernel;
    var $id;
    var $values;
    var $error;
    var $actual_id;

    function __construct($kernel, $id = 0)
    {
        $this->kernel = &$kernel;
        $this->id = intval($id);
        $this->error = new Intraface_Error;
        $this->actual_id = 0;

        if ($this->id != 0) {
            $this->load();
        }
    }

    function load()
    {
        $db = new DB_sql;

        $db->query("SELECT id, user_name, important, description, DATE_FORMAT(from_date_time, '%d-%m-%Y %H:%i') AS dk_from_date_time, DATE_FORMAT(to_date_time, '%d-%m-%Y %H:%i') AS dk_to_date_time FROM systemmessage_disturbance WHERE active = 1 AND id = ".$this->id);
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['dk_from_date_time'] = $db->f('dk_from_date_time');
            $this->value['dk_to_date_time'] = $db->f('dk_to_date_time');
            $this->value['user_name'] = $db->f('user_name');
            $this->value['important'] = $db->f('important');
            $this->value['description'] = $db->f('description');

        } else {
            $this->id = 0;
        }

    }

    function update($input)
    {
        $input = safeToDb($input);

        $validator = new Intraface_Validator($this->error);

        $from = split(' ', $input['from_date_time']);

        if ($validator->isDate($from[0], 'Ugyldig dato i Fra tidspunkt')) {
            $db_from = new Intraface_Date($from[0]);
            $db_from->convert2db();
        }
        $validator->isTime($from[1], 'Ugyldigt tidspunkt i Fra tidspunkt');

        $to = split(' ', $input['to_date_time']);
        if ($validator->isDate($to[0], 'Ugyldig dato i Til tidspunkt')) {
            $db_to = new Intraface_Date($to[0]);
            $db_to->convert2db();
        }
        $validator->isTime($to[1], 'Ugyldigt tidspunkt i Til tidspunkt');

        $validator->isString($input['description'], 'Beskrivelsen er ikke udfyldt korrekt');

        if (isset($input['important']) && $input['important'] = 'true') {
            $input['important'] = 1;
        } else {
            $input['important'] = 0;
        }

        if ($this->error->isError()) {
            return 0;
        }

        $sql = "user_name = \"".$this->kernel->user->getAddress()->get('name')."\",
            from_date_time = \"".$db_from->get()." ".$from[1]."\",
            to_date_time = \"".$db_to->get()." ".$to[1]."\",
            important = \"".$input['important']."\",
            description = \"".$input['description']."\",
            active = 1";

        $db = new DB_sql;

        if ($this->id != 0) {
            $db->query("UPDATE systemmessage_disturbance SET ".$sql." WHERE id = ".$this->id);
        } else {
            $db->query("INSERT INTO systemmessage_disturbance SET date_created = NOW(), ".$sql);
            $this->id = $db->insertedId();
        }

        return $this->id;

    }

    function delete()
    {
        if ($this->id == 0) {
            return 0;
        }

        $db = new DB_sql;

        $db->query("UPDATE systemmessage_disturbance SET active = 0 WHERE id = ".$this->id);

        return 1;
    }

    function getActual()
    {
        $db = new DB_sql;
        $db->query("SELECT id, user_name, important, description, DATE_FORMAT(from_date_time, '%d-%m-%Y %H:%i') AS dk_from_date_time, DATE_FORMAT(to_date_time, '%d-%m-%Y %H:%i') AS dk_to_date_time FROM systemmessage_disturbance WHERE active = 1 AND from_date_time < NOW() AND to_date_time > NOW() ORDER BY from_date_time DESC");
        if (!$db->nextRecord()) {
            //$this->actual_id = 0;
            return array();
        }
        $value['id'] = $db->f('id');
        $value['dk_from_date_time'] = $db->f('dk_from_date_time');
        $value['dk_to_date_time'] = $db->f('dk_to_date_time');
        $value['user_name'] = $db->f('user_name');
        $value['important'] = $db->f('important');
        $value['description'] = $db->f('description');

        //$this->actual_id = $db->f('id');

        return $value;
    }

    function getList($only_actual = false)
    {
        $db = new DB_sql;

        $value = array();
        $i = 0;

        if ($only_actual == true) {
            $sql = "AND to_date_time > NOW() AND id != ".$this->actual_id;
        } else {
            $sql = "";
        }

        $db->query("SELECT id, user_name, important, description, DATE_FORMAT(from_date_time, '%d-%m-%Y %H:%i') AS dk_from_date_time, DATE_FORMAT(to_date_time, '%d-%m-%Y %H:%i') AS dk_to_date_time FROM systemmessage_disturbance WHERE active = 1 ".$sql." ORDER BY from_date_time DESC");
        while ($db->nextRecord()) {

            $value[$i]['id'] = $db->f('id');
            $value[$i]['dk_from_date_time'] = $db->f('dk_from_date_time');
            $value[$i]['dk_to_date_time'] = $db->f('dk_to_date_time');
            $value[$i]['user_name'] = $db->f('user_name');
            $value[$i]['important'] = $db->f('important');
            $value[$i]['description'] = $db->f('description');

            $i++;
        }

        return $value;
    }
}
