<?php
/**
 * IntranetMainenance
 *
 * This class is used by the administrators to change settings
 * for the intranet.
 *
 * It incorporates the MDB2-database.
 *
 * @package Intraface_IntranetMaintenance
 * @author	Sune Jensen <sj@sunet.dk>
 * @author	Lars Olesen <lars@legestue.net>
 *
 * @version	@package-version@
 *
 */
require_once 'Intraface/Intranet.php';
require_once 'Intraface/DBQuery.php';

class IntranetMaintenance extends Intranet
{

    var $db; // databaseobject mdb2
    var $id; // intranet id
    var $address; // address object

    /**
     * Constructor
     *
     * @todo remove kernel - it is only used for dbquery and for getting the
     *       intranet, seems quite stupid considering it extends intranet and
     *       for a random key :)
     */
    function __construct($kernel, $intranet_id = 0)
    {
        if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error('intranetmaintenance needs a kernel', E_USER_ERROR);
        }

        $this->db = MDB2::singleton(DB_DSN);

        $this->id = (int)$intranet_id;
        $this->kernel = $kernel;
        $this->error = new Error();

        if ($this->id > 0) {
            Intranet::__construct($intranet_id);
            $this->load();
        }
    }

    function createDBQuery()
    {
        $this->dbquery = new DBQuery($this->kernel, 'intranet');
    }

    function flushAccess()
    {
        if ($this->id == 0) {
            trigger_error('cannot flush access because no id i set', E_USER_ERROR);
        }
        // Sletter alle permissions som har med intranettet - og kun intranettet at gøre.
        $this->db->query("DELETE FROM permission WHERE user_id = 0 AND intranet_id = ".intval($this->id));
        return 1;
    }

    public function setModuleAccess($module_id)
    {
        if ($this->id == 0) {
            trigger_error('cannot set access because no id i set in IntranetMaintenance->setModuleAccess', E_USER_ERROR);
            exit;
        }

        if (!is_numeric($module_id)) {
            $result = $this->db->query("SELECT id FROM module WHERE name=".$this->db->quote($module_id, 'text'));
            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $module_id = $row['id'];
            } else {
                trigger_error("intranet maintenance says unknown module in IntranetMaintenance->setModuleAccess", E_USER_ERROR);
                exit;
            }
        }

        $result = $this->db->query("SELECT id FROM module WHERE id = ".intval($module_id));
        if(PEAR::isError($result)) {
            trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
            exit;
        }
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $result = $this->db->exec("INSERT INTO permission SET
                intranet_id = ".intval($this->id).",
                user_id = 0,
                module_id = ".intval($row['id']));
            if(PEAR::isError($result)) {
                trigger_error('Error in exec: '.$result->getUserInfo(), E_USER_INFO);
            }
            return $result;
        }
        else {
            trigger_error("intranet maintenance says unknown module_id in IntranetMaintenance->setModuleAccess", E_USER_ERROR);
            exit;
        }
    }

    public function removeModuleAccess($module_id)
    {
        if ($this->id == 0) {
            trigger_error('cannot remove access because no id i set in IntranetMaintenance->removeModuleAccess', E_USER_ERROR);
            exit;
        }

        if (!is_numeric($module_id)) {
            $result = $this->db->query("SELECT id FROM module WHERE name=".$this->db->quote($module_id, 'text'));
            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $module_id = $row['id'];
            } else {
                trigger_error("intranet maintenance says unknown module in IntranetMaintenance->removeModuleAccess", E_USER_ERROR);
                exit;
            }
        }

        $res = $this->db->query("SELECT id FROM module WHERE id = ".intval($module_id));
        if ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $delete = $this->db->exec("DELETE FROM permission WHERE
                intranet_id = ".intval($this->id)." AND
                module_id = ".intval($row['id']));

            if(PEAR::isError($delete)) {
                trigger_error('Error in delete: '.$delete->getUserInfo(), E_USER_ERROR);
                exit;
            }

            return true;
        }
        else {
            trigger_error("intranet maintenance says unknown module_id in IntranetMaintenance->removeModuleAccess", E_USER_ERROR);
            exit;
        }
    }

    function validate($input)
    {
        $validator = new Validator($this->error);

        $validator->isString($input["name"], "Navn skal være en streng", "", "");
        if ($validator->isNumeric($input["maintained_by_user_id"], "Vedligeholder er ugyldig", "zero_or_greater")) {
            $temp_user = new User($input["maintained_by_user_id"]);

            if (!$temp_user->hasIntranetAccess($this->kernel->intranet->get('id'))) {
                $this->error->set("Ugyldig bruger som vedligeholder");
            }
        }
        if ($this->error->isError()) {
            return false;
        }
        return true;
    }


    /**
     * This method will only update a few parameters in the intranet.
     */

    function save($input)
    {
        if (!is_array($input)) {
            trigger_error('input is not an array', E_USER_ERROR);
        }

        if (!$this->validate($input)) {
            return false;
        }

        $sql = "name = \"".$this->db->escape($input["name"])."\",
            maintained_by_user_id = ".intval($input["maintained_by_user_id"])."";

        if (isset($input["identifier"])) {
            $sql .= ", identifier = \"".$this->db->escape($input['identifier'])."\"";
        }
        if ($this->id == 0 || isset($input["generate_private_key"])) {
            $sql .= ", private_key = \"".$this->db->escape($this->kernel->randomKey(50))."\"";
        }

        if ($this->id == 0 || isset($input["generate_public_key"])) {
            $sql .= ", public_key = \"".$this->kernel->randomKey(15)."\"";
        }

        if ($this->id == 0) {
            $this->db->query("INSERT INTO intranet SET ".$sql.", date_changed = NOW()");
            $this->id = $this->db->lastInsertID('intranet', 'id');

            if (PEAR::isError($this->id)) {
                   trigger_error("Error in IntranetMaintenance: ".$id->getMessage(), E_USER_ERROR);
            }

            // @todo this seems quite strange :)
            print('ff'.$this->id.'ff');

            $this->load();
        } else {
            $this->db->query("UPDATE intranet SET ".$sql.", date_changed = NOW() WHERE id = ".intval($this->id));
            $this->load();
        }
        return true;
    }

    function getList()
    {

        if ($this->dbquery->checkFilter('text')) {
            $this->dbquery->setCondition('name LIKE "%'.safeToHtml($this->dbquery->getFilter('text')).'%"');
        }

        if ($this->dbquery->checkFilter('user_id')) {
            $this->dbquery->setJoin('LEFT', 'permission', 'permission.intranet_id = intranet.id', '');
            $this->dbquery->setCondition('permission.user_id = '.$this->dbquery->getFilter('user_id'));
        }

        $this->dbquery->setSorting('name');

        $i = 0;
        $intranet = array();
        $db = $this->dbquery->getRecordset('DISTINCT(intranet.id), intranet.name');
        while ($db->nextRecord()) {
            $intranet[$i]['id'] = $db->f('id');
            $intranet[$i]['name'] = $db->f('name');
            $i++;
        }


        /*
         * GOOD but I needed to implement with DBQuery
        $i = 0;
        $res = &$this->db->query("SELECT id, name FROM intranet ORDER BY name");
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $intranet[$i] = $row;
            $i++;
        }
        */

        return $intranet;
    }

    function setContact($contact_id)
    {
        $this->db->query("UPDATE intranet SET contact_id = ".intval($contact_id)." WHERE id = " . intval($this->id));
        return true;
    }

}

?>
