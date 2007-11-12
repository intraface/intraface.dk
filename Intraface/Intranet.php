<?php
/**
 * Styrer hvilket intranet man arbejder i
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 * @version 002
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Address.php';

class Intranet extends Standard
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
    public $id; // intranet id. HACK It has to be public othervise it can not be changed from IntranetMaintenance that inherit from this.
                // @todo that is not correct. It just have to be protected instead.

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
        $this->error = new Error();

        if(!$this->load()) {
            trigger_error('unknown intranet', E_USER_ERROR);
        }
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

        if(PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }

        if($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->value = $row;
            /*
            $this->value['id'] = $db->f('id');
            $this->value['name'] = $db->f('name');
            $this->value['identifier'] = $db->f('identifier');
            $this->value['key_code'] = $db->f('key_code');
            $this->value['public_key'] = $db->f('public_key');
            $this->value['contact_id'] = $db->f('contact_id');
            $this->value['private_key'] = $db->f('private_key');
            $this->value['pdf_header_file_id'] = $db->f('pdf_header_file_id'); // egentlig burde dette vel bare være en indstilling i settings?
            $this->value['maintained_by_user_id'] = $db->f('maintained_by_user_id');
            */
            $this->address = Address::factory('intranet', $this->id);
            return $this->id;
        } else {
            // $this->address = Address::factory('intranet', 0);
            $this->id = 0;
            return 0;
        }
        $result->free();
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

        if(is_string($module)) {
            if (empty($this->modules)) {
                $result = $this->db->query("SELECT id, name FROM module WHERE active = 1");
                while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $this->modules[$row['name']] = $row['id'];
                }
                $result->free();
            }

            if (!empty($this->modules[$module])) {
                $module_id = $this->modules[$module];
            } else {
                trigger_error('intranet says invalid module name '.$module, E_USER_ERROR);
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
}
?>
