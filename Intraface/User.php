<?php
/**
 * User and rights management
 *
 * NOTICE:
 * Keep in mind the relation between User.php, UserAdministration.php and
 * UserMaintenance.php
 *
 * User.php is ONLY for the function that the normal user is allowed to. That
 * means NOT create other users. The user should not be allowed to change is
 * own rights.
 *
 * UserAdministration.php is for the administrator of the intranet. Can create
 * new user. Administrator is not allowed to disable a User, as it will affect
 * all intranets.
 *
 * UserMaintenance.php is for overall maintenance team. Should be allowed everthing.
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
require_once 'Intraface/Standard.php';
require_once 'MDB2.php';

class User extends Standard
{
    /**
     * @var db
     */
    protected $db;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    public $value;

    /**
     * @var integer
     */
    protected $intranet_id;

    /**
     * @var error
     */
    public $error;

    /**
     * @var array
     */
    protected $permissions = array();

    /**
     * @var array
     */
    protected $modules = array();

    /**
     * @var address
     */
    private $address;

    /**
     * @var boolean
     */
    private $permissions_loaded = false;

    /**
     * Constructor
     *
     * @param integer $id User id
     *
     * @return void
     */
    public function __construct($id = 0)
    {
        $this->id          = $this->value['id'] = intval($id);
        $this->db          = MDB2::singleton(DB_DSN);
        $this->intranet_id = 0;         // @todo hvad laver den her?
        $this->error       = $this->getError();

        if (PEAR::isError($this->db)) {
            throw new Exception($this->db->getMessage() . $this->db->getUserInfo());
        }

        if ($this->id > 0) {
            $this->load();
        }
    }

    public function getError()
    {
        if ($this->error) {
            return $this->error;
        }
        require_once 'Intraface/Error.php';
        return ($this->error = new Error);
    }

    /**
     * Load
     *
     * @return void
     */
    protected function load()
    {
        $result = $this->db->query("SELECT id, email, disabled FROM user WHERE id = " . $this->db->quote($this->id, 'integer'));
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        if ($result->numRows() == 1) {
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            $this->value = $row;
            return $this->id;
        } else {
            return ($this->id = 0);
        }
    }

    /**
     * Gets the address object
     *
     * @return object
     */
    public function getAddress()
    {
        if (!empty($this->address)) {
            return $this->address;
        }
        require_once 'Intraface/Address.php';
        return ($this->address = Address::factory('user', $this->id));
    }

    /**
     * Gets permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Loads permissions
     *
     * @param integer $intranet_id
     *
     * @return boolean
     */
    private function loadPermissions($intranet_id = null)
    {
        if (!$intranet_id) {
            $intranet_id = $this->intranet_id;
        }

        $result = $this->db->query("SELECT intranet_id, module_id
            FROM permission
            WHERE permission.intranet_id = ". $this->db->quote($intranet_id, 'integer')."
                AND permission.user_id = ". $this->db->quote($this->get('id'), 'integer'));

        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
            return false;
        }

        while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->permissions['intranet']['module'][$row['module_id']] = true;
            $this->permissions['user']['module'][$row['module_id']] = true;
            $this->permissions['user']['intranet'][$row['intranet_id']] = true;
        }

        $this->permissions_loaded = true;
        return true;
    }

    /**
     * Gets module id from string
     *
     * @param integer $module
     *
     * @return integer
     */
    private function getModuleIdFromString($module)
    {
        if (empty($this->modules)) {
            $result = $this->db->query("SELECT id, name FROM module WHERE active = 1");
            if (PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }

            while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $this->modules[$row['name']] = $row['id'];
            }
        }
        if (!empty($this->modules[$module])) {
            return $module_id = $this->modules[$module];
        } else {
           trigger_error('user says unknown module ' . $module, E_USER_ERROR);
        }
    }

    /**
     * Clears cached permissions
     *
     * @return void
     */
    public function clearCachedPermission()
    {
        $this->permissions = array();
        $this->modules = array();
        $this->permissions_loaded = false;
    }

    /**
     * Returns whether the permissions has been loaded
     *
     * @return boolean
     */
    private function permissionsLoaded()
    {
        return $this->permissions_loaded;
    }

    /**
     * Returns whether the user has intranetaccess
     *
     * @param integer $intranet_id
     *
     * @return boolean
     */
    public function hasIntranetAccess($intranet_id = 0)
    {
        if ($intranet_id == 0) {
            $intranet_id = $this->intranet_id;
        }

        //if (!$this->permissionsLoaded()) {
            $this->loadPermissions($intranet_id);
        //}

        if (!empty($this->permissions['user']['intranet'][$intranet_id])) {
            return $this->permissions['user']['intranet'][$intranet_id];
        }

        return false;
    }

    /**
     * Returns whether user has module Access
     *
     * @param integer $module
     * @param integer $intranet_id
     *
     * @return integer
     */
    public function hasModuleAccess($module, $intranet_id = 0)
    {
        $intranet_id = intval($intranet_id);

        if ($intranet_id == 0) {
            $intranet_id = $this->intranet_id;
        }

        if (!$this->permissionsLoaded()) {
            $this->loadPermissions($intranet_id);
        }

        // getting the module
        if (is_string($module)) {
            $module_id = $this->getModuleIdFromString($module);
        } else {
            $module_id = intval($module);
        }

        if (!empty($this->permissions) AND is_array($this->permissions)) {
            if (empty($this->permissions['intranet']['module'][$module_id]) OR $this->permissions['intranet']['module'][$module_id] !== true) {
                return false;
            } else if (empty($this->permissions['user']['module'][$module_id]) OR $this->permissions['user']['module'][$module_id] !== true) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns whether user has subaccess
     *
     * @param integer $module
     * @param integer $sub_access
     * @param integer intranet_id (når den skal tilgås fra intranetmaintenance (til hvad?)
     *
     * @return boolean
     */
    public function hasSubAccess($module, $sub_access, $intranet_id = 0)
    {
        settype($intranet_id, "integer");
        if ($intranet_id == 0) {
            $intranet_id = $this->intranet_id;
        }

        if (is_string($module)) {
            $module_id = $this->getModuleIdFromString($module);
        } else {
            $module_id = intval($module);
        }

        if (is_string($sub_access)) {
            $result = $this->db->query("SELECT id FROM module_sub_access WHERE module_id = ".$module_id." AND name = \"".$sub_access."\"");
            if (PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            if ($row = $result->fetchRow()) {
                $sub_access_id = $row['id'];
            } else {
                trigger_error("user says unknown subaccess", E_USER_ERROR);
            }
        } else {
            $sub_access_id = intval($sub_access);
        }

        // If the permissions are not loaded, we will do that.
        if (empty($this->permissions['intranet']['module'])) {
            // Vi tjekker om intranettet har adgang til modullet.
            // er den ikke unødvendig - det kan vi vel lave i den næste
            // sql-sætning?
            $result = $this->db->query("SELECT module.id
                FROM permission
                INNER JOIN module
                    ON permission.module_id = module.id
                WHERE permission.intranet_id = ".$intranet_id."
                    AND permission.user_id = 0");
            if (PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            while($row = $result->fetchRow()) {
                $this->permissions['intranet']['module'][$row['id']];
            }
        }

        // first we check whether the use has access to the module.
        if (empty($this->permissions['intranet']['module'][$module_id]) OR $this->permissions['intranet']['module'][$module_id] !== true) {
            return false;
        }

        // then we check whether there is access to the sub access
        if (!empty($this->permissions['user']['module']['subaccess'][$sub_access_id]) AND $this->permissions['user']['module']['subaccess'][$sub_access_id] === true) {
            return true;
        }

        // if the check on the array did not go possitive, we make sure it is because they are not loaded.
        // @todo: this is probably not a good way to do it.
        $sql = "SELECT module_sub_access.id
            FROM permission
            INNER JOIN module_sub_access
                ON permission.module_sub_access_id = module_sub_access.id
            INNER JOIN module
                ON permission.module_id = module.id
            WHERE permission.intranet_id = ".$intranet_id."
                AND permission.user_id = ".$this->id."
                AND module.id = ".$module_id."
                AND module_sub_access.module_id = module.id";

        $result = $this->db->query($sql);
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        while ($row = $result->fetchRow()) {
            $this->permissions['user']['module']['subaccess'][$row['id']] = true;
        }

        if (!empty($this->permissions['user']['module']['subaccess'][$sub_access_id]) AND $this->permissions['user']['module']['subaccess'][$sub_access_id] === true) {
            return true;
        }

        return false;
    }

    /**
     * Returns the active intranet
     *
     * @return integer
     */
    public function getActiveIntranetId()
    {
        $result = $this->db->query("SELECT active_intranet_id FROM user WHERE id = ".$this->db->quote($this->id, 'integer'));
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }

        if ($result->numRows() == 1) {
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            if ($this->hasIntranetAccess($row['active_intranet_id'])) {
                return $row['active_intranet_id'];
            }
        }

        $result = $this->db->query("SELECT intranet.id FROM intranet
            INNER JOIN permission ON permission.intranet_id = intranet.id
            WHERE permission.user_id = ".$this->id);
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return $row['id'];
        } else {
            return false;
        }

    }

    /**
     * Sets intranet_id
     *
     * @todo what is this used for?
     *
     * @return boolean
     */
    public function setIntranetId($id)
    {
        $this->intranet_id = intval($id);
        if ($this->id == 0 || $this->hasIntranetAccess()) {
            $this->load();
            return true;
        } else {
            trigger_error('you do not have access to this intranet', E_USER_ERROR);
            return false;
        }
    }

    /**
     * Sets active intranet_id
     *
     * @return boolean
     */
    public function setActiveIntranetId($id)
    {
        $id = intval($id);
        if ($this->hasIntranetAccess($id)) {
            $this->db->exec("UPDATE user SET active_intranet_id = ". $this->db->quote($id, 'integer')." WHERE id = ". $this->db->quote($this->get('id'), 'integer'));
            return $id;
        }
        return false;
    }

    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Gets a list with the users intranets
     *
     * @return array
     */
    public function getIntranetList()
    {
        // Skal denne funktion være her? Måske den istedet skulle være i intranet.
        $result = $this->db->query("SELECT DISTINCT(intranet.id), intranet.name FROM intranet
            INNER JOIN permission
                ON permission.intranet_id = intranet.id
            WHERE permission.user_id = ".$this->id);
        $i = 0;

        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        return $result->fetchAll();
    }

    /**
     * Validates user info
     *
     * NOTICE: As it is created now, $input has to be injected by reference,
     * because of the little hack with disabled.
     *
     * @param array $input
     *
     * @return boolean
     */
    protected function validate(&$input)
    {
        $input = safeToDb($input);
        require_once 'Intraface/Validator.php';
        $validator = new Validator($this->error);

        $validator->isEmail($input["email"], "Ugyldig E-mail");
        $result = $this->db->query("SELECT id FROM user WHERE email = \"".$input["email"]."\" AND id != ".$this->id);
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        if ($result->numRows() > 0) {
            $this->error->set("E-mail-adressen er allerede benyttet");
        }

        if (isset($input["disabled"])) {
            $input["disabled"] = 1;
        } else {
            $input["disabled"] = 0;
        }

    }

    /**
     * Updates the user
     *
     * @param array $input Data to update
     *
     * @return integer
     */
    public function update($input)
    {
        $this->validate($input);

        $sql = "email = \"".$input["email"]."\",
            disabled = ".$input["disabled"]."";

        if ($this->error->isError()) {
            return false;
        }

        if ($this->id) {
            $this->db->exec("UPDATE user SET ".$sql." WHERE id = ".$this->id);
            $this->load();
            return $this->id;
        } else {
            trigger_error("An id is needed to update user details in User->Update()", E_USER_ERROR);
        }
    }

    /**
     * Ved ikke om der skal bygges noget mere sikkerhed ind i denne?
     *
     * @todo this should be an observer instead
     *
     */
    public function SendForgottenPasswordEmail($email)
    {
        if (!Validate::email($email)) {
            return false;
        }
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query("SELECT id FROM user WHERE email = '".$email."'");
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        if ($result->numRows() != 1) {
            return false;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $new_password = Intraface_Kernel::randomKey(8);


        $db->exec("UPDATE user SET password = '".md5($new_password)."' WHERE id =" . $row['id']);

        $subject = 'Tsk, glemt din adgangskode?';

        $body  = "Huha, det var heldigt, at vi stod på spring i kulissen, så vi kan hjælpe dig med at lave en ny adgangskode.\n\n";
        $body .= "Din nye adgangskode er: " . $new_password . "\n\n";
        $body .= "Du kan logge ind fra:\n\n";
        $body .= "<".PATH_WWW.">\n\n";
        $body .= "Med venlig hilsen\nDin hengivne webserver";

        if (mail($email, $subject, $body, "From: Intraface.dk <robot@intraface.dk>\nReturn-Path: robot@intraface.dk")) {
            return true;
        }
    }

    public function updatePassword($old_password, $new_password, $repeat_password)
    {
        if ($this->id == 0) {
            return false;
        }

        $result = $this->db->query("SELECT * FROM user WHERE password = '".safeToDb(md5($old_password))."' AND id = " . $this->get('id'));
        if ($result->numRows() < 1) {
            $this->error->set('error in old password');
        }

        $validator = new Validator($this->error);
        $validator->isPassword($new_password, 6, 16, "error in new password");

        if ($new_password != $repeat_password) {
            $this->error->set('error in password');
        }

        if ($this->error->isError()) {
            return false;
        }

        $this->db->query("UPDATE user SET password = '".safeToDb(md5($new_password))."' WHERE id = " . $this->get('id'));

        return true;

    }

    /**
     * TODO Måske kan det gøres enklere, så der ikke skal bruges så mange tabeller
     */
    public function getList()
    {
        $i = 0;
        $result = $this->db->query("SELECT DISTINCT user.id, user.email, address.name
            FROM user
            INNER JOIN permission ON permission.user_id = user.id
            LEFT JOIN address ON user.id = address.belong_to_id AND address.type = 2
            WHERE (address.active = 1 OR address.type IS NULL) AND permission.intranet_id = ".$this->intranet_id."
            ORDER BY address.name");

        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        return $result->fetchAll();
    }

    public function isFilledIn()
    {
        if ($this->getAddress()->get('phone')) {
            return true;
        }
        return false;
    }

    public function getId()
    {
        return $this->id;
    }
}