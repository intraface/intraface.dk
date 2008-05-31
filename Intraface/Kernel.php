<?php
/**
 * Kernel - a registry
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Intraface/Weblogin.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/Main.php';
require_once 'Intraface/Shared.php';
require_once 'Intraface/Setting.php';

class Intraface_Kernel
{
    private $db;
    public $intranet;
    public $user;
    private $primary_module_name;
    private $_session;
    public $session_id;
    public $modules = array();
    public $shared;
    public $translation;
    private $observers = array();

    /**
     * Constructor
     *
     * @param string $session Session string
     *
     * @return void
     */
    function __construct($session = null)
    {
        if ($session == NULL) {
            $this->_session = md5(uniqid(rand(), true));
        } else {
            $this->_session = $session;
        }
        $this->db = MDB2:: singleton(DB_DSN);
        if (PEAR::isError($this->db)) {
            trigger_error($this->db->getMessage() . $this->db->getUserInfo(), E_USER_ERROR);
        }
    }

    /**
     * returns an unique user id for this login
     *
     * @todo: session_id is not the correct name, as this is not always session id.
     */
    function getSessionId()
    {
        return $this->_session;
    }

    /**
     * @param integer $id The user id to created
     *
     * @return User object
     */
    function createUser($id)
    {
        return new Intraface_User($id);
    }

    /**
     * @param integer $id The intranet id to created
     *
     * @return Intranet object
     */
    function createIntranet($id)
    {
        return new Intranet($id);
    }

    /**
     * @param integer $intranet_id The intranet id
     * @param integer $user_id     The user id
     *
     * @return Setting object
     */
    function createSetting($intranet_id, $user_id = 0)
    {
        return new Setting($intranet_id, $user_id);

    }

    /**
     * @param string $session_id The session id
     *
     * @return Weblogin object
     */
    function createWeblogin($session_id)
    {
        return new Weblogin($session_id);
    }

    /**
     * Should not be used anymore
     *
     * @param string $type       Type for the login
     * @param string $key        The key for the login
     * @param string $session_id Session id
     *
     * @deprecated
     */
    function weblogin($type, $key, $session_id)
    {
        require_once 'Weblogin.php';

        if ($type == 'private') {

            $result = $this->db->query("SELECT id FROM intranet WHERE private_key = " . $this->db->quote($key, 'text'));
            if (PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            if ($result->numRows() == 0) {
                return ($intranet_id = false);
            }
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            $intranet_id = $row['id'];

        } elseif ($type == 'public') {

            $result = $this->db->query("SELECT id FROM intranet WHERE public_key = '".$key."'");
            if (PEAR::isError($result)) {
                trigger_error($result->getUserInfo(), E_USER_ERROR);
            }
            if ($result->numRows() == 0) {
                return ($intranet_id = false);
            }
            $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            $intranet_id = $row['id'];

        } else {
            trigger_error('Ugyldig type weblogin', E_USER_ERROR);
        }

        if ($intranet_id === false) {
            return false;
        } else {
            $this->intranet = $this->createIntranet($intranet_id);
            $this->setting = $this->createSetting($this->intranet->get('id'));
        }
        $this->weblogin = $this->createWeblogin($session_id);

        $this->_session = $session_id;

        return true;

    }

    /**
     * Sets primary module for a page
     *
     * @param string $module_name Name on module
     *
     * @return Module object
     */
    function module($module_name)
    {
        if (!empty($this->primary_module_object) AND is_object($this->primary_module_object)) {
            trigger_error('Primary module is already set', E_USER_ERROR);
        } else {

            $module = $this->useModule($module_name);
            if (is_object($module)) {
                $this->primary_module_name = $module_name;

                // @todo Finder dependent moduller - Dette kunne flyttes til useModule, hvorfor er den egentlig ikke det? /Sune 06-07-2006
                $dependent_modules = $module->getDependentModules();

                for ($i = 0, $max = count($dependent_modules); $i < $max; $i++) {
                    $no_use = $this->useModule($dependent_modules[$i]);
                }

                return $module;
            } else {
                // @todo Den fejlmeddelse er egentlig irrelevant, da useModul ikke enten returnere et objekt eller trigger_error.
                trigger_error('Du har ikke adgang til modulet', E_USER_ERROR);
                return false;
            }
        }
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

    /**
     * Gets a module
     *
     * @param string $name of the module
     *
     * @return module object or false
     */
    function getModule($name)
    {
        if (!empty($this->modules[$name]) AND is_object($this->modules[$name])) {
            return($this->modules[$name]);
        } else {
            trigger_error('Ugyldigt modulnavn '.$name.' eller modulet er ikke loadet i funktionen getModule: '.$name, E_USER_ERROR);
        }
    }

    /**
     * Gets a list of modules - is used on frontpage and under rights
     *
     * @param string $order_by which index
     *
     * @return array with modules
     */
    function getModules($order_by = 'frontpage_index')
    {
        $modules = array();

        if ($order_by != '') {
            $order_by = "ORDER BY ".$this->db->quoteIdentifier($order_by);
        }

        $i = 0;
        $result = $this->db->query("SELECT id, menu_label, name, show_menu FROM module WHERE active = 1 ".$order_by);
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $modules[$i]['id'] = $row['id'];
            $modules[$i]['name'] = $row['name'];
            $modules[$i]['menu_label'] = $row['menu_label'];
            $modules[$i]['show_menu'] = $row['show_menu'];

            $j = 0;

            if (!isset($sub_modules)) {
                $sub_modules = array();

                //$result_sub = $db->query("SELECT id, description, module_id FROM module_sub_access WHERE active = 1 AND module_id = ".$db->quote($row["id"], 'integer')." ORDER BY description");
                $result_sub = $this->db->query("SELECT id, description, module_id FROM module_sub_access WHERE active = 1 ORDER BY description");
                if (PEAR::isError($result_sub)) {
                    trigger_error($result_sub->getUserInfo(), E_USER_ERROR);
                }
                // $modules[$i]['sub_access'] = $result_sub->fetchAll();

                while ($row_sub = $result_sub->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $sub_modules[$row_sub['module_id']][$row_sub['id']]['id'] = $row_sub['id'];
                    $sub_modules[$row_sub['module_id']][$row_sub['id']]['description'] = $row_sub['description'];
                }
            }
            // $row['id'] er module_id
            if (!empty($sub_modules[$row['id']]) AND count($sub_modules[$row['id']]) > 0) {
                foreach ($sub_modules[$row['id']] AS $sub_module) {
                    $modules[$i]['sub_access'][$j]['id'] = $sub_module['id'];
                    $modules[$i]['sub_access'][$j]['description'] = $sub_module['description'];
                    $j++;
                }
            }

            $i++;
        }
        return $modules;

    }

    /**
     * Use another module besides the primary
     *
     * @param string  $module_name	      Navn p� det modullet der skal loades
     * @param boolean $ignore_user_access Ved true, tjekker den ikke om brugeren har adgang, men kun om intranettet har. Benyttes bla. til n�r der skal tr�kkes vare fra lageret fra gennem faktura.
     *
     * @return object or false Hvis man har adgang returnere den et object, ellers returnere den 0;
     */
    function useModule($module_name, $ignore_user_access = false)
    {
        if (!ereg("^[a-z0-9]+$", $module_name)) {
            trigger_error('kernel says invalid module name '.$module_name, E_USER_ERROR);
            return false;
        }

        // Tjekker om modullet allerede er loaded
        if (!empty($this->modules[$module_name]) AND is_object($this->modules[$module_name])) {
            return $this->modules[$module_name];
        }

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

        if ($access == true) {
            $main_class_name = 'Main' . ucfirst($module_name);
            $main_class_path = PATH_INCLUDE_MODULE . $module_name . '/' . $main_class_name . '.php';

            if (file_exists($main_class_path)) {
                require_once $main_class_path;
                $object = new $main_class_name;
                $object->load($this);
                $this->modules[$module_name] = $object;
                return $object;
            } else {
                trigger_error($main_class_path.' do not exist', E_USER_ERROR);
            }
        } else {
            throw new Exception('You need access to the required module '.$module_name.' to see this page');
            trigger_error('Du mangler adgang til et modul for at kunne se denne side: '.$module_name, E_USER_ERROR);
        }
    }

    /**
     * Public: Giv adgang til et shared
     *
     * @param string $shared_name Navn p� det shared der skal loades
     *
     * @return object or 0 Hvis man har adgang returnere den et object, ellers returnere den 0;
     */
    function useShared($shared_name)
    {

        if (!ereg("^[a-z0-9]+$", $shared_name)) {
            trigger_error('Ugyldig shared '.$shared_name, E_USER_ERROR);
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
            trigger_error($shared_name . ' cannot be found on ' . $main_shared_path . ' with PATH_INCLUDE_SHARED: ' . PATH_INCLUDE_SHARED, E_USER_ERROR);
        }
    }

    /**
     * Returns translation object and sets page_id
     * Could be moved when there is no more calls to the method.
     *
     * @param string $page_id Which specific translation object is needed
     *
     * @return Translation object
     */
    function getTranslation($page_id = 'common')
    {
        if (is_object($this->translation)) {
            if (!empty($page_id)) {
                $this->translation->setPageID($page_id);
            }
            return $this->translation;
        }

        if (isset($this->translation)) {
            $this->translation->setPageID($page_id);
        }

        return $this->translation;
    }

    /**
     * Function to make a random key - e.g. for passwords
     * This functions don't return any characters whick can be mistaken.
     * Won't return 0 (zero) or o (as in Ole) or 1 (one) or l (lars), because they can be mistaken on print.
     *
     * @param $count (integer) how many characters to return?
     *
     * @return 	random key (string) only letters
     */
    function randomKey($length = 1)
    {
        // Legal characters
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789';
        $how_many = strlen($chars);
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;

        while ($i < $length) {
            $num = rand() % $how_many;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
}