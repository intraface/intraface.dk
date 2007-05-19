<?php
require_once 'Intraface/3Party/Database/Db_sql.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Main.php';

class ModuleMaintenance
{
    var $id;
    var $db;
    var $kernel;
    var $value;
    var $error;
    var $sub_access;

    function __construct($kernel, $id = 0)
    {
        $this->id = intval($id);
        $this->db = new DB_Sql;
        $this->kernel = $kernel;
        $this->error = new Error;
        $this->value = array();

        $this->load();
    }

    function load()
    {

        // Starter med at nustille
        $this->value = array();
        $this->sub_access;

        if ($this->id != 0) {
            $this->db->query("SELECT * FROM module WHERE id = ".$this->id);
            if ($this->db->nextRecord()) {
                $this->value["id"] = $this->db->f("id");
                $this->value["name"] = $this->db->f("name");
                $this->value["menu_label"] = $this->db->f("menu_label");
                $this->value["show_menu"] = $this->db->f("show_menu");
                $this->value["active"] = $this->db->f("active");

                $this->sub_access = new SubAccessMaintenance($this);

            }
        }
    }

    function registerModule($module_name) {
        $primary_module = $this->kernel->getPrimaryModule();
        $db = new DB_Sql;
        $updated_sub_access_id = array();

        $main_class_name = "Main".ucfirst($module_name);
        $main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";

        if (!file_exists($main_class_path)) {
            $this->error->set("Filen ".$main_class_path." eksistere ikke!");
            // $msg[] = $main_class_path." eksistere ikke!";
        } else {
            //if ($module_name == $primary_module->getName()) {
                // Hvis modullet er det vi arbejder i nu
                //$module = $primary_module;
            //} else {
                include_once $main_class_path;
                $module = new $main_class_name;
            //}

            if (!is_object($module)) {
                $this->error->set($main_class_name." kunne ikke initialiseres!");
                // $msg[] = $main_class_name." kunne ikke initialiseres!";
            } else {
                // her kan vi oprette tabellerne nødvendige for det enkelte modul i stedet for at have dem i starten.

                $sql = "menu_label = \"".$module->menu_label."\",
                            show_menu = ".$module->show_menu.",
                            active = ".$module->active.",
                            menu_index = ".intval($module->menu_index).",
                            frontpage_index = ".intval($module->frontpage_index);

                $db->query("SELECT id FROM module WHERE name = \"".$module_name."\"");
                if ($db->nextRecord()) {
                    $module_id = $db->f("id");
                    $db->query("UPDATE module SET ".$sql." WHERE id = ".$module_id);
                    $module_msg[$module_id] = "Opdateret";
                } else {
                    $db->query("INSERT INTO module SET name = \"".$module_name."\", ".$sql);
                    $module_id = $db->insertedId();
                    $module_msg[$module_id] = "Registreret";
                }
                $db->free();

                $updated_module_id = $module_id;

                // print("med følgende sub access: ");
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
                            // print($module->sub_access[$i].", ");
                }
            }
        }

        return array('module_msg' => $module_msg, 'updated_module_id' => $updated_module_id, 'updated_sub_access_id' => $updated_sub_access_id);
    }

    function register()
    {

        $msg = array();
        $module_msg = array();
        $db = new DB_Sql;
        $updated_sub_access_id = array();

        if ($handle = opendir(PATH_INCLUDE_MODULE)) {
            $updated_module_id = array();
            $updated_sub_access_id = array();

            //$primary_module = $this->kernel->getPrimaryModule();

            while (false !== ($module_name = readdir($handle))) {
                if (substr($module_name, 0, 1) == ".") {
                    continue; // starter forfra på næste directory
                }

                if (substr($module_name, 0, 5) == "_old_") {
                    // Det er et slettet modul - det får lov at blive uden en besked
                    continue;
                }

                if (!ereg("^[a-z0-9]+$", $module_name)) {
                    $this->error->set($module_name." er et ugyldigt navn");
                    // $msg[] = $module_name." er et ugyldigt navn";
                    continue; // starter forfra på næste directory
                }

                $updated = $this->registerModule($module_name);

                $updated_module_id[] = $updated['updated_module_id'];
                $updated_sub_access_id = array_merge($updated_sub_access_id, $updated['updated_sub_access_id']);
                $module_msg = array_merge($module_msg, $updated['module_msg']);

            }

            // Sætte alle moduler som ikke længere eksistere til active = 0
            if (count($updated_module_id) > 0) {
                $db->query("UPDATE module SET active = 0 WHERE id NOT IN (".implode(",", $updated_module_id).")");
                $module_msg[0] = $db->affectedRows()." moduler er fjernet og blevet deaktiveret.<br />";
            }

            if (count($updated_sub_access_id) > 0) {
                $db->query("UPDATE module_sub_access SET active = 0 WHERE id NOT IN (".implode(",", $updated_sub_access_id).")");
                $module_msg[0] .= $db->affectedRows()." sub access' er fjernet og blevet deaktiveret.";
            }
        }
        return $module_msg;
    }

    function getList() {

        $db = new DB_Sql;

        $i = 0;
        $this->db->query("SELECT id, name, menu_label, show_menu, menu_index, frontpage_index FROM module WHERE active = 1 ORDER BY menu_index");

        while ($this->db->nextRecord()) {
            $value[$i]["id"] = $this->db->f("id");
            $value[$i]["name"] = $this->db->f("name");
            $value[$i]["menu_label"] = $this->db->f("menu_label");
            $value[$i]["show_menu"] = $this->db->f("show_menu");
            $value[$i]["menu_index"] = $this->db->f("menu_index");
            $value[$i]["frontpage_index"] = $this->db->f("frontpage_index");

            $j = 0;
            $db->query("SELECT id, name, description FROM module_sub_access WHERE active = 1 AND module_id = ".$this->db->f("id")." ORDER BY description");
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

    function get($key = '') {
        if (!empty($key)) {
            return($this->value[$key]);
        } else {
            return $this->value;
        }
    }
}

?>