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
        /*
        $db = MDB2::singleton(DB_DSN);
        if (PEAR::isError($db)) {
            throw new Exception("Error in creating db: ".$db->getUserInfo());
            exit;
        }
        $result = $db->query("SELECT id FROM module WHERE name = ".$db->quote($name, 'text'));
        if (PEAR::isError($result)) {
            throw new Exception("Error in query: ".$result->getUserInfo());
            exit;
        }

        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {

            return new ModuleMaintenance($row['id']);
        } else {

            throw new Exception("invalid module name ".$name."!");
        }
        */
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
                exit;
            }

            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $this->value = $row;

                // $this->sub_access = new SubAccessMaintenance($this);

                $j = 0;
                $result_sub_access = $this->db->query("SELECT id, name, description FROM module_sub_access WHERE active = 1 AND module_id = ".$row["id"]." ORDER BY description");
                if (PEAR::isError($result_sub_access)) {
                    throw new Exception("Error in query: ".$result_sub_access->getUserInfo());
                    exit;
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
        /*
        $db = new DB_Sql;
        $updated_sub_access_id = array();
        $module_msg = array();
        $updated_module_id = 0;

        $main_class_name = "Main".ucfirst($module_name);
        $main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";


        if (!file_exists($main_class_path)) {
            $this->error->set("Filen ".$main_class_path." eksistere ikke!");

        } else {
            include_once $main_class_path;
            $module = new $main_class_name;

            if (!is_object($module)) {
                $this->error->set($main_class_name." kunne ikke initialiseres!");
            } else {
                // her kan vi oprette tabellerne n�dvendige for det enkelte modul i stedet for at have dem i starten.

                if (empty($module->menu_label) && empty($module->active) && empty($module->menu_index)) {
                    $this->error->set('Properties for module "'.$module_name.'" er ikke loadet. Kontrol er constructor er sat rigtigt op i modulet');
                }
                else {
                    $sql = "menu_label = \"".$module->getMenuLabel()."\",
                                show_menu = ".$module->getShowMenu().",
                                active = ".$module->isActive().",
                                menu_index = ".intval($module->getMenuIndex()).",
                                frontpage_index = ".intval($module->getFrontpageIndex());

                    $db->query("SELECT id FROM module WHERE name = \"".$module_name."\"");
                    if ($db->nextRecord()) {
                        $module_id = $db->f("id");
                        $db->query("UPDATE module SET ".$sql." WHERE id = ".$module_id);
                        $module_msg[$module_name] = "Opdateret";
                    } else {
                        $db->query("INSERT INTO module SET name = \"".$module_name."\", ".$sql);
                        $module_id = $db->insertedId();
                        $module_msg[$module_name] = "Registreret";
                    }
                    $db->free();

                    $updated_module_id = $module_id;
                    $count_subaccess = count($module->sub_access);

                    for ($i = 0; $i < $count_subaccess; $i++) {
                        $db->query("SELECT id FROM module_sub_access WHERE module_id = ".$module_id." AND name = \"".$module->sub_access[$i]."\"");
                        if ($db->nextRecord()) {
                            $updated_sub_access_id[] = $db->f('id');
                            $db->query("UPDATE module_sub_access SET description = \"".$module->sub_access_description[$i]."\", active = 1 WHERE id = ".$db->f("id"));
                        } else {
                            $db->query("INSERT INTO module_sub_access SET module_id = ".$module_id.", name = \"".$module->sub_access[$i]."\", description = \"".$module->sub_access_description[$i]."\", active = 1");
                            $updated_sub_access_id[] = $db->insertedId();
                        }
                        $db->free();
                    }
                }
            }
        }

        return array('module_msg' => $module_msg, 'updated_module_id' => $updated_module_id, 'updated_sub_access_id' => $updated_sub_access_id);
        */
    }

    public function register()
    {
        $gateway = new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
        return $gateway->registerAll();
        /*
        $msg = array();
        $module_msg = array();
        $db = new DB_Sql;
        $updated_sub_access_id = array();

        if ($handle = opendir(PATH_INCLUDE_MODULE)) {
            $updated_module_id = array();
            $updated_sub_access_id = array();

            while (false !== ($module_name = readdir($handle))) {
                if (substr($module_name, 0, 1) == ".") {
                    continue; // starter forfra p� n�ste directory
                }

                if (substr($module_name, 0, 5) == "_old_") {
                    // Det er et slettet modul - det f�r lov at blive uden en besked
                    continue;
                }

                if (!ereg("^[a-z0-9]+$", $module_name)) {
                    $this->error->set($module_name." er et ugyldigt navn");
                    // $msg[] = $module_name." er et ugyldigt navn";
                    continue; // starter forfra p� n�ste directory
                }

                $updated = $this->registerModule($module_name);

                $updated_module_id[] = (int)$updated['updated_module_id'];
                $updated_sub_access_id = array_merge($updated_sub_access_id, $updated['updated_sub_access_id']);
                $module_msg = array_merge($module_msg, $updated['module_msg']);
            }

            // S�tte alle moduler som ikke l�ngere eksistere til active = 0
            if (count($updated_module_id) > 0) {
                $db->query("UPDATE module SET active = 0 WHERE id NOT IN (".implode(",", $updated_module_id).")");
                $module_msg['update'] = $db->affectedRows()." moduler er fjernet og blevet deaktiveret.<br />";
            }

            if (count($updated_sub_access_id) > 0) {
                $db->query("UPDATE module_sub_access SET active = 0 WHERE id NOT IN (".implode(",", $updated_sub_access_id).")");
                $module_msg['update'] .= $db->affectedRows()." sub access' er fjernet og blevet deaktiveret.";
            }
        }

        return $module_msg;
        */
    }
    /*
    public function getList()
    {

        $db = new DB_Sql;

        $i = 0;
        $result = $this->db->query("SELECT id, name, menu_label, show_menu, menu_index, frontpage_index FROM module WHERE active = 1 ORDER BY menu_index");
        if (PEAR::isError($result)) {
            throw new Exception("Error in query: ".$result->getUserInfo());
            exit;
        }

        while ($row = $result->fetchRow()) {
            $value[$i] = $row;

            $j = 0;
            $db->query("SELECT id, name, description FROM module_sub_access WHERE active = 1 AND module_id = ".$row['id']." ORDER BY description");
            while ($db->nextRecord()) {
                $value[$i]["sub_access"][$j]["id"] = $db->f("id");
                $value[$i]["sub_access"][$j]["name"] = $db->f("name");
                $value[$i]["sub_access"][$j]["description"] = $db->f("description");
                $j++;
            }
            $i++;
        }

        return $value;
    }
    */

    public function get($key = '')
    {
        if (!empty($key)) {
            return($this->value[$key]);
        } else {
            return $this->value;
        }
    }
}
