<?php

/**
 * Maintain users and user rights
 * Please read in User.php for description of relations
 *
 * @package Intraface_IntranetMaintenance
 * @author	Sune Jensen <sj@sunet.dk>
 * @author	Lars Olesen <lars@legestue.net>
 * @since	0.1.0
 * @version	@package-version@
 *
 *
 */

class UserMaintenance extends User {

    public $kernel;

    function __construct($kernel, $user_id = 0) {
        $this->kernel = $kernel;
        parent::__construct($user_id);

    }

    /**
     * create DBQuery object
     * 
     * @param object kernel
     * @return void none
     */
    public function createDBQuery($kernel) 
    {
        $this->dbquery = new DBQuery($kernel, 'user');
        $this->dbquery->setJoin('LEFT', 'address', 'user.id = address.belong_to_id AND address.type = 2', 'address.active = 1 OR address.active IS NULL');
        
    }
    /**
     * This function makes it possible to create a new User as User.php do not allow that.
     */
    function update($input) {

        $this->validate($input);
        $validator = new Validator($this->error);

        if($this->id == 0) {
            $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal");
        }
        else {
            $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal", "allow_empty");
        }

        $sql = "email = \"".$input["email"]."\",
            disabled = ".$input["disabled"]."";

        if(!empty($input["password"])) {
            if($input["password"] === $input["confirm_password"]) {
                $sql .= ", password = \"".md5($input["password"])."\"";
            }
            else {
                $this->error->set("De to adgangskoder er ikke ens!");
            }
        }

        if($this->error->isError()) {
            return false;
        }

        if($this->id) {
            $this->db->exec("UPDATE user SET ".$sql." WHERE id = ".$this->id);
            $this->load();
            return $this->id;
        }
        else {
            $this->db->exec("INSERT INTO user SET ".$sql);
            $this->id = $this->db->lastInsertId();
            $this->load();
            return $this->id;
        }
    }

    /*
    function updateEmail($email) {
        $email = new Email($email);
        if(!$email->validate()) {
            $this->values["email"] = $email->get();
            $this->errormsg = "Ugyldigt e-mail adresse.";
            return(0);
        }
        else {
            $this->db->query("SELECT id FROM user WHERE email = \"".$email->get()."\" AND id != ".$this->id);
            if($this->db->nextRecord()) {
                $this->values["email"] = $email->get();
                $this->errormsg = "E-mail-adressen er allerede benyttet.";
                return(0);
            }
            else {
                if($this->id) {
                    $this->db->query("UPDATE user SET email = \"".$email->get()."\" WHERE id = ".$this->id);
                    $this->load();
                    return($this->id);
                }
                else {
                    $this->db->query("INSERT INTO user SET email = \"".$email->get()."\", password = \"\"");
                    $this->id = $this->db->insertedId();
                    $this->load();
                    return($this->id);
                }
            }
        }
    }
    */

    /*
    function updatePassword($password, $confirm_password) {
        if($this->id == 0) {
            trigger_error("Kan ikke opdater password uden brugeren er oprettet!", FATAL);
        }
        elseif(strlen($password) < 6) {
            $this->errormsg = "Adgangskoden skal være på mindst 6 tegn.";
            return(0);
        }
        elseif($password != $confirm_password) {
            $this->errormsg = "De to adgangskoder er ikke ens.";
            return(0);
        }
        else {
            $this->db->query("UPDATE user SET password = \"".md5($password)."\" WHERE id = ".$this->id);
            return(1);
        }
    }
    */


    /*
    function getIntranetArray() {
        $i = 0;
        $array = array();
        $this->db->query("SELECT * FROM intranet ORDER BY name");
        while($this->db->nextRecord()) {
            if($this->hasIntranetAccess($this->db->f("id"))) {
                $array[$i]["id"] = $this->db->f("id");
                $array[$i]["name"] = $this->db->f("name");
                $i++;
            }
        }
        return($array);
    }
    */


    /*
    function hasIntranetAccess($intranet_id) {
        $db = new Db_sql;
        settype($intranet_id, "integer");
        $db->query("SELECT intranet.id
            FROM permission
            INNER JOIN intranet
            ON permission.intranet_id = intranet.id
            WHERE intranet.id = ".$intranet_id." AND permission.user_id = ".$this->id."");
        return($db->nextRecord());
    }
    */

    /*
    function hasModuleAccess($intranet_id, $module_id) {
        $db = new Db_sql;
        settype($intranet_id, "integer");
        settype($module_id, "integer");

        $sql = "SELECT permission.id
            FROM permission
            INNER JOIN module
                ON permission.module_id = module.id
            WHERE permission.intranet_id = ".$intranet_id."
                AND permission.user_id = ".$this->id."
                AND module.id = ".$module_id;

        $db->query($sql);
        return ($db->nextRecord());
    }
    */

    /*
    function hasSubAccess($intranet_id, $module_id, $sub_access_id) {
        $db = new Db_sql;
        settype($intranet_id, "integer");
        settype($module_id, "integer");
        settype($sub_access_id, "integer");

        $sql = "SELECT permission.id
            FROM permission
            INNER JOIN module_sub_access
                ON permission.module_sub_access_id = module_sub_access.id
            INNER JOIN module
                ON permission.module_id = module.id
            WHERE permission.intranet_id = ".$intranet_id."
                AND permission.user_id = ".$this->id."
                AND module.id = ".$module_id."
                AND module_sub_access.id = ".$sub_access_id."
                AND module_sub_access.module_id = module.id";

        $db->query($sql);
        return ($db->nextRecord());
    }
    */

    /**
     * Fjerner alle access til denne bruger for det satte intranet
     *
     */

    function flushAccess() {
        $db = new Db_sql;
        $db->query("DELETE FROM permission WHERE user_id = ".$this->id." AND intranet_id = ".$this->intranet_id);
    }



    function setIntranetAccess($intranet_id = 0) {
        $db = new Db_sql;
        settype($intranet_id, "integer");
        if($intranet_id == 0) {
            if($this->intranet_id == 0) {
                trigger_error("Der er ikke angivet et intranet id", E_USER_ERROR);
            }
            else {
                $intranet_id = $this->intranet_id;
            }
        }

        $db->query("SELECT id FROM intranet WHERE id = ".$intranet_id);
        if($db->nextRecord()) {
            $db->query("SELECT id FROM permission WHERE intranet_id = ".$intranet_id." AND user_id = ".$this->id." AND module_id = 0 AND module_sub_access_id = 0");
            if($db->nextRecord()) {
                return($db->f("id"));
            }
            else {
                $db->query("INSERT INTO permission SET intranet_id = ".$intranet_id.", user_id = ".$this->id);
                return($db->insertedId());
            }
        }
        else {
            trigger_error("Ugyldig intranet id", E_USER_ERROR);
        }
    }

    /**
     * Hvad er det den her returnerer?
     */

    function setModuleAccess($module_id, $intranet_id = 0) {


        $db = new Db_sql;
        settype($intranet_id, "integer");

        if($intranet_id == 0) {
            if($this->intranet_id == 0) {
                trigger_error("Der er ikke angivet et intranet id", E_USER_ERROR);
            }
            else {
                $intranet_id = $this->intranet_id;
            }

        }

        $module_name = $module_id;

        if (!is_numeric($module_id)) {

            $db->query("SELECT id FROM module WHERE name =  '".$module_id."'");
            if (!$db->nextRecord()) {
                trigger_error("Ugyldig module_id", E_USER_ERROR);
            }
            $module_id = $db->f('id');
        }

        $module_id = intval($module_id);

        $db->query("SELECT id FROM module WHERE id = ".$module_id);
        if($db->nextRecord()) {
            $db->query("SELECT id FROM permission WHERE intranet_id = ".$intranet_id." AND user_id = ".$this->id." AND module_id = ".$module_id." AND module_sub_access_id = 0");
            if($db->nextRecord()) {
                return($db->f("id"));
            }
            else {
                $id = $this->setIntranetAccess($intranet_id);
                $db->query("UPDATE permission SET module_id = ".$module_id." WHERE id = ".$id);
                return($id);
            }
        }
        else {
            trigger_error("Ugyldig module_id '".$module_id."/".$module_name."'", E_USER_ERROR);
        }
    }


    function setSubAccess($module_id, $sub_access_id, $intranet_id = 0) {
        $db = new Db_sql;


        if (!is_numeric($module_id)) {
            $db->query("SELECT id FROM module WHERE name =  '".$module_id."'");
            if (!$db->nextRecord()) {
                trigger_error("Ugyldig module_id", E_USER_ERROR);
            }
            $module_id = $db->f('id');
        }

        if (!is_numeric($sub_access_id)) {
            $db->query("SELECT id FROM module_sub_access WHERE name =  '".$sub_access_id."'");
            if (!$db->nextRecord()) {
                trigger_error("Ugyldig module_id", E_USER_ERROR);
            }
            $sub_access_id = $db->f('id');
        }

        settype($intranet_id, "integer");
        settype($module_id, "integer");
        settype($sub_access_id, "integer");

        if($intranet_id == 0) {
            if($this->intranet_id == 0) {
                trigger_error("Der er ikke angivet et intranet id", E_USER_ERROR);
            }
            else {
                $intranet_id = $this->intranet_id;
            }
        }

        $db->query("SELECT id FROM module_sub_access WHERE module_id = ".$module_id." AND id = ".$sub_access_id);
        if($db->nextRecord()) {
            $id = $this->setModuleAccess($module_id, $intranet_id);
            $db->query("UPDATE permission SET module_sub_access_id = ".$sub_access_id." WHERE id = ".$id);
            return($id);
        }
        else {
            trigger_error("Ugyldig sub_access_id i useradmin->setSubAccess()", E_USER_ERROR);
        }
    }

    function getList() {

        if($this->intranet_id != 0) {
            return User::getList();
        }

        if($this->dbquery->checkFilter('text')) {
            $this->dbquery->setCondition('address.name LIKE "%'.safeToDB($this->dbquery->getFilter('text')).'%" OR user.email LIKE "%'.safeToDB($this->dbquery->getFilter('text')).'%"');
        }

        $this->dbquery->setSorting('address.name');

        $db = $this->dbquery->getRecordset('address.name, user.id, user.email', '', false);
        $i = 0;
        $user = array();
        while($db->nextRecord()) {
            $user[$i]["id"] = $db->f("id");
            $user[$i]["email"] = $db->f("email");
            $user[$i]["name"] = $db->f("name");
            $i++;

        }

        return($user);

    }
}

?>