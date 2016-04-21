<?php
/**
 * // @todo could probably extend ModuleHandler, and therefore not need the
 *          constant pointing to the modules.
@package Intraface_IntranetMaintenance
 */
class ModuleMaintenance
{
    private $id;
    private $db;
    private $value;
    public $error;
    private $sub_access;

    public function __construct($id = 0)
    {
        $this->id = intval($id);
        $this->db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($this->db)) {
            throw new Exception("Error in creating db: ".$this->db->getUserInfo());
        }

        $this->error = new Intraface_Error;
        $this->value = array();

        $this->load();
    }

    static function factory($name)
    {
        $gateway = new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
        return $gateway->findByName($name);
    }

    private function load()
    {
        // Starter med at nustille
        $this->value = array();
        $this->sub_access;
        if ($this->id != 0) {
            $result = $this->db->query("SELECT * FROM module WHERE id = ".$this->id);
            if (PEAR::isError($result)) {
                throw new Exception("Error in query: ".$result->getUserInfo());
            }

            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $this->value = $row;

                // $this->sub_access = new SubAccessMaintenance($this);

                $j = 0;
                $result_sub_access = $this->db->query("SELECT id, name, description FROM module_sub_access WHERE active = 1 AND module_id = ".$row["id"]." ORDER BY description");
                if (PEAR::isError($result_sub_access)) {
                    throw new Exception("Error in query: ".$result_sub_access->getUserInfo());
                }
                $i = 0;
                $this->value["sub_access"] = array();
                while ($row = $result_sub_access->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $this->value["sub_access"][$i] = $row;
                    $i++;
                }
            }
        }
    }

    public function registerModule($module_name)
    {
        $gateway = new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
        return $gateway->registerByName($module_name);
    }

    public function register()
    {
        $gateway = new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
        return $gateway->registerAll();
    }

    public function get($key = '')
    {
        if (!empty($key)) {
            return($this->value[$key]);
        } else {
            return $this->value;
        }
    }
}
