<?php
/**
 * Styrer hvilket intranet man arbejder i
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 * @version 002
 */
class Intraface_Intranet extends Intraface_Standard
{
    /**
     * @var object
     */
    public $address;

    /**
     * @var array
     */
    public $value;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var object
     */
    private $db;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * Constructor
     *
     * @param integer $intranet_id The id of the intranet
     *
     * @return void
     */
    function __construct($id)
    {
        $this->id = intval($id);
        $this->db = MDB2::singleton(DB_DSN);
        $this->error = new Intraface_Error();

        if (!$this->load()) {
            throw new Exception('unknown intranet');
        }
    }

    function getName()
    {
        return $this->get('name');
    }

    /**
     * loads
     *
     * @return void
     */
    function load()
    {
        $this->db = MDB2::singleton(DB_DSN);
        $result = $this->db->query("SELECT
                id,
                name,
                identifier,
                key_code,
                public_key,
                contact_id,
                private_key,
                pdf_header_file_id,
                maintained_by_user_id
            FROM intranet
            WHERE id = ".$this->db->quote($this->id, 'integer'));

        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->value   = $row;
            $this->address = $this->getAddress();
            return $this->id;
        } else {
            $this->id = 0;
            return 0;
        }
        $result->free();
    }

    public function getAddress()
    {
    	return ($this->address = Intraface_Address::factory('intranet', $this->id));
    }

    /**
     * Returns whether the intranet has access to the module
     *
     * @todo might be smarter to throw in an actual module object
     *       that would make us sure that it is actually valid
     *
     * @param mixed $module The id or name of the module
     *
     * @return void
     */
    function hasModuleAccess($module)
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

        if (is_string($module)) {
            if (empty($this->modules)) {
                $result = $this->db->query("SELECT id, name FROM module WHERE active = 1");
                while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $this->modules[$row['name']] = $row['id'];
                }
                $result->free();
            }

            if (!empty($this->modules[$module])) {
                $module_id = $this->modules[$module];
            } else {
                throw new Exception('intranet says invalid module name '.$module);
            }
        } else {
            $module_id = intval($module);
        }

        if (!empty($this->permissions)) {
            if (!empty($this->permissions['intranet']['module'][$module_id]) AND $this->permissions['intranet']['module'][$module_id] == true) {
                return true;
            }
            return false;
        }

        $result = $this->db->query("SELECT module_id FROM permission WHERE intranet_id = ".$this->db->quote($this->id, 'integer')." AND user_id = 0");
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->permissions['intranet']['module'][$row['module_id']] = true;
        }
        $result->free();

        if (!empty($this->permissions['intranet']['module'][$module_id]) AND $this->permissions['intranet']['module'][$module_id] == true) {
            return true;
        }
        return false;
    }

    /**
     * Returns the id of the intranet
     *
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }
}