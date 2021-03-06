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
require_once 'Intraface/functions.php';

class Intraface_User extends Intraface_Standard implements Intraface_Identity
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
    protected $intranet_id = 0;

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
        return ($this->error = new Intraface_Error);
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
            throw new Exception($result->getUserInfo());
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
        return ($this->address = Intraface_Address::factory('user', $this->id));
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
            throw new Exception($result->getUserInfo());
        }

        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
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
                throw new Exception($result->getUserInfo());
            }

            while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $this->modules[$row['name']] = $row['id'];
            }
        }
        if (!empty($this->modules[$module])) {
            return $module_id = $this->modules[$module];
        } else {
            throw new Exception('user says unknown module ' . $module);
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
        $filename = PATH_INCLUDE_MODULE . $module . '/Main' . ucfirst($module) . '.php';
        if (file_exists($filename)) {
            require_once $filename;
            $module_class = 'Main'.ucfirst($module);
            $module_object = new $module_class;
            if ($module_object->isShared()) {
                return true;
            }
            if ($module_object->isRequired()) {
                return true;
            }
        }

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

        if (!empty($this->permissions) and is_array($this->permissions)) {
            if (empty($this->permissions['intranet']['module'][$module_id]) or $this->permissions['intranet']['module'][$module_id] !== true) {
                return false;
            } elseif (empty($this->permissions['user']['module'][$module_id]) or $this->permissions['user']['module'][$module_id] !== true) {
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
     * @param integer intranet_id (n�r den skal tilg�s fra intranetmaintenance (til hvad?)
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
                throw new Exception($result->getUserInfo());
            }
            if ($row = $result->fetchRow()) {
                $sub_access_id = $row['id'];
            } else {
                throw new Exception("user says unknown subaccess");
            }
        } else {
            $sub_access_id = intval($sub_access);
        }

        // If the permissions are not loaded, we will do that.
        if (empty($this->permissions['intranet']['module'])) {
            // Vi tjekker om intranettet har adgang til modullet.
            // er den ikke un�dvendig - det kan vi vel lave i den n�ste
            // sql-s�tning?
            $result = $this->db->query("SELECT module.id
                FROM permission
                INNER JOIN module
                    ON permission.module_id = module.id
                WHERE permission.intranet_id = ".$intranet_id."
                    AND permission.user_id = 0");
            if (PEAR::isError($result)) {
                throw new Exception($result->getUserInfo());
            }
            while ($row = $result->fetchRow()) {
                $this->permissions['intranet']['module'][$row['id']];
            }
        }

        // first we check whether the use has access to the module.
        if (empty($this->permissions['intranet']['module'][$module_id]) or $this->permissions['intranet']['module'][$module_id] !== true) {
            return false;
        }

        // then we check whether there is access to the sub access
        if (!empty($this->permissions['user']['module']['subaccess'][$sub_access_id]) and $this->permissions['user']['module']['subaccess'][$sub_access_id] === true) {
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
            throw new Exception($result->getUserInfo());
        }
        while ($row = $result->fetchRow()) {
            $this->permissions['user']['module']['subaccess'][$row['id']] = true;
        }

        if (!empty($this->permissions['user']['module']['subaccess'][$sub_access_id]) and $this->permissions['user']['module']['subaccess'][$sub_access_id] === true) {
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
            throw new Exception($result->getUserInfo());
        }

        if ($result->numRows() == 1) {
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            if ($this->hasIntranetAccess($row['active_intranet_id']) and $row['active_intranet_id'] != 0) {
                return $row['active_intranet_id'];
            }
        }

        $result = $this->db->query("SELECT intranet.id
            FROM intranet
            INNER JOIN permission
                ON permission.intranet_id = intranet.id
            WHERE permission.user_id = " . $this->db->quote($this->id, 'integer'));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return $row['id'];
        } else {
            return false;
        }
    }

    function getActiveIntranet()
    {
        return new Intraface_Intranet($this->getActiveIntranetId());
    }

    function getSetting()
    {
        return new Intraface_Setting($this->getActiveIntranet()->getId(), $this->getId());
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
        }
        throw new Exception('you do not have access to this intranet');
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
        // Skal denne funktion v�re her? M�ske den istedet skulle v�re i intranet.
        $result = $this->db->query("SELECT DISTINCT(intranet.id), intranet.name FROM intranet
            INNER JOIN permission
                ON permission.intranet_id = intranet.id
            WHERE permission.user_id = ".$this->id);

        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
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
        $validator = new Intraface_Validator($this->error);

        $validator->isEmail($input["email"], "Ugyldig E-mail");
        $result = $this->db->query("SELECT id FROM user WHERE email = \"".$input["email"]."\" AND id != ".$this->id);
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
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
            throw new Exception("An id is needed to update user details in User->Update()");
        }

        return true;
    }

    function generateNewPassword($email)
    {
        if (!Validate::email($email)) {
            return false;
        }
        $db = MDB2::singleton(DB_DSN);
        $result = $db->query("SELECT id FROM user WHERE email = '".$email."'");
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        if ($result->numRows() != 1) {
            return false;
        }
        $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
        $new_password = Intraface_Kernel::randomKey(8);

        $db->exec("UPDATE user SET password = '".md5($new_password)."' WHERE id =" . $row['id']);

        return $new_password;
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

        $validator = new Intraface_Validator($this->error);
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
     * TODO M�ske kan det g�res enklere, s� der ikke skal bruges s� mange tabeller
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
            throw new Exception($result->getUserInfo());
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

    function getLanguage()
    {
        return $this->getSetting()->get('user', 'language');
    }
}
