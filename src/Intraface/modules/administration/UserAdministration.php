<?php
/**
 *
 * Administration of user data and rights
 * Please read in User.php for description of relations
 *
 * @package Intraface_Administration
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version     @package-version@
 *
 *
 */
class UserAdministration extends Intraface_User
{
    function __construct($kernel, $id)
    {
        parent::__construct($id);
    }

    /**
     * @todo why use this instead of the one in user?
     */
    function update($input)
    {
        $this->validate($input);
        $validator = new Intraface_Validator($this->error);

        if (!empty($input["password"])) {
            if ($this->id == 0) {
                $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal");
            } else {
                $validator->isPassword($input["password"], 6, 16, "Ugyldig adgangskode. Den skal være mellem 6 og 16 tegn, og må indeholde store og små bogstaver samt tal", "allow_empty");
            }
        }

        $sql = "email = \"".$input["email"]."\"";

        if (!empty($input["password"])) {
            if ($input["password"] === $input["confirm_password"]) {
                $sql .= ", password = \"".md5($input["password"])."\"";
            } else {
                $this->error->set("De to adgangskoder er ikke ens!");
            }
        }

        if ($this->error->isError()) {
            return false;
        }

        if ($this->id) {
            $this->db->exec("UPDATE user SET ".$sql." WHERE id = ".$this->id);
            $this->load();
            return $this->id;
        } else {
            $this->db->exec("INSERT INTO user SET ".$sql);
            $this->id = $this->db->lastInsertId();
            $this->load();
            return $this->id;
        }
    }
}
