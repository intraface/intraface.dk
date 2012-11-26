<?php
/**
 * Module
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @author  Sune Jensen <sj@sunet.dk>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_ModuleHandler
{
    private $user;
    private $internet;
    private $modules = array();
    private $primary_module_name;
    private $primary_module_object;
    private $shared = array();

    public function __construct($intranet = '', $user = '')
    {
        $this->user = $user;
        $this->intranet = $intranet;
    }

    /**
     * Returns the primary module
     *
     * Used for instance in Page to give the correct submenu.
     *
     * @return module object or false
     */
    function getPrimaryModule()
    {
        if (!empty($this->modules[$this->primary_module_name]) AND is_object($this->modules[$this->primary_module_name])) {
            return($this->modules[$this->primary_module_name]);
        } else {
            return false;
        }
    }

    function setPrimaryModule($module_name)
    {
        if (!empty($this->primary_module_object) AND is_object($this->primary_module_object)) {
            throw new Exception('Primary module has been set');
        } else {
            $module = $this->useModule($module_name);

            if (is_object($module)) {
                $this->primary_module_name = $module_name;

                // Finds dependent modules
                // @todo Maybe this should be moved to useModule()
                $dependent_modules = $module->getDependentModules();

                foreach ($dependent_modules as $dependent) {
                    $no_use = $this->useModule($dependent);
                }

                return $module;
            } else {
                // @todo Den fejlmeddelse er egentlig irrelevant, da useModul ikke enten returnere et objekt eller throw new Exception.
                throw new Exception('Du har ikke adgang til modulet');
            }
        }

    }

    /**
     * useModule()
     *
     * @todo Need to handle access
     *
     * @param  string  $module_name
     * @param  boolean $ignore_user_access
     *
     * @return object  $module
     */
    public function useModule($module_name, $ignore_user_access = false)
    {
        if (!self::isValidModuleName($module_name)) {
            throw new Exception($module_name . ' is not a valid module name');
        }

        if (!empty($this->modules[$module_name]) AND is_object($this->modules[$module_name])) {
            return $this->modules[$module_name];
        }

        $this->modules[$module_name] = $module_name;

        // @todo check whether a module has any limitations in access here? - we have not asked for shared

        // access control here
        $access = false;

        if (!is_object($this->user)) {
            if (!is_object($this->intranet)) {
                throw new Exception('Cannot use a module when no intranet is available');
            }
            // Det er et weblogin.
            if ($this->intranet->hasModuleAccess($module_name)) {
                $access = true;
            }
        } elseif ($ignore_user_access) {
            if (!is_object($this->intranet)) {
                throw new Exception('Cannot use a module when no intranet is available');
            }
            // Skal kun kontrollere om intranettet har adgang, for at benytte modullet
            if ($this->intranet->hasModuleAccess($module_name)) {
                $access = true;
            }
        } else {
            // Almindelig login
            if ($this->user->hasModuleAccess($module_name)) {
                $access = true;
            }
        }

        if ($access !== true) {
            throw new Exception('You need access to a required module to see this page, maybe it is ' . $module_name);
        }

        $main_class_name = "Main".ucfirst($module_name);
        $main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";

        if (file_exists($main_class_path)) {
            require_once($main_class_path);
            $object = new $main_class_name;
            $object->load($this);
            $this->modules[$module_name] = $object;
            return $object;
        } else {
            throw new Exception('ModuleHandler: ' . $main_class_path . ' does not exist');
        }
    }

    /**
     * Gives access to a shared module
     *
     * @param string $shared_name Name on module to load
     *
     * @return object or false
     */
    public function useShared($shared_name)
    {
        if (!self::isValidModuleName($shared_name)) {
            throw new Exception($shared_name. ' is not a valid shared module name');
        }

        // Tjekker om shared allerede er loaded
        if (!empty($this->shared[$shared_name]) AND is_object($this->shared[$shared_name])) {
            return $this->shared[$shared_name];
        }

        $main_shared_name = 'Shared' . ucfirst($shared_name);
        $main_shared_path = PATH_INCLUDE_SHARED . $shared_name . '/' . $main_shared_name . '.php';

        if (file_exists($main_shared_path)) {
            require_once $main_shared_path;
            $object = new $main_shared_name;
            $object->load();
            $this->shared[$shared_name] = $object;
            return $object;
        } else {
            throw new Exception($shared_name . ' cannot be found on ' . $main_shared_path . ' with PATH_INCLUDE_SHARED: ' . PATH_INCLUDE_SHARED);
        }
    }

    /**
     * getModule()
     *
     * @param string $module_name
     *
     * @return object
     */
    function getModule($name)
    {
        if (is_object($this->modules[$name])) {
            return($this->modules[$name]);
        } else {
            throw new Exception('getModule() module ' . $name . ' not loaded');
        }
    }

    /**
     * getModules()
     *
     * @param string $order_by
     *
     * @return array
     */
    static public function getModules($db, $order_by = 'frontpage_index')
    {
        $modules = array();

        if ($order_by != '') {
            $order_by = "ORDER BY ".$db->quoteIdentifier($order_by, 'text');
        }

        $i = 0;
        $result = $db->query("SELECT id, menu_label, name, show_menu FROM module WHERE active = 1 ".$order_by);
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $modules[$i]['id'] = $row['id'];
            $modules[$i]['name'] = $row['name'];
            $modules[$i]['menu_label'] = $row['menu_label'];
            $modules[$i]['show_menu'] = $row['show_menu'];

            $j = 0;
            $result_sub = $db->query("SELECT id, description FROM module_sub_access WHERE active = 1 AND module_id = ".$db->quote($row["id"], 'integer')." ORDER BY description");
            if (PEAR::isError($result_sub)) {
                throw new Exception($result_sub->getUserInfo());
            }

            while ($row_sub = $result_sub->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $modules[$i]['sub_access'][$j]['id'] = $row_sub['id'];
                $modules[$i]['sub_access'][$j]['description'] = $row_sub['description'];
                $j++;
            }

            $i++;
        }
        return $modules;

    }

    /**
     * Function to check whether the module has been registered
     * Made temporarily for /main/index.php
     */
    static public function exists($db, $module_id)
    {
        if (is_numeric($module_id)) {
            throw new Exception("Not yet implemented!");
            exit;
        } else {
            $result = $db->query('SELECT id FROM module WHERE name = '.$db->quote($module_id, 'text'));
            if (PEAR::isError($result)) {
                throw new Exception('Error in query: '.$result->getUserInfo());
            }

            if ($result->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    private static function isValidModuleName($name)
    {
        return preg_match("/^[a-z0-9]+$/", $name);
    }
}
