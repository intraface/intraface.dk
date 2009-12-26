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
class Intraface_Kernel
{
    private $db;
    public $intranet;
    public $user;
    private $_session;
    public $session_id;
    /*
    public $modules = array();
    public $shared;
    private $primary_module_name;
    */
    public $translation;
    private $observers = array();
    private $modulehandler;

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

    function getModuleHandler()
    {
        if (!empty($this->modulehandler)) {
        	return $this->modulehandler;
        }
    	return ($this->modulehandler = new Intraface_ModuleHandler($this->intranet, $this->user));
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
        return $this->getModuleHandler()->setPrimaryModule($module_name);
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
        return $this->getModuleHandler()->getPrimaryModule();
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
        return $this->getModuleHandler()->getModule($name);
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
        return $this->getModuleHandler()->getModules($this->db, $order_by);
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
        return $this->getModuleHandler()->useModule($module_name, $ignore_user_access = false);
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
        return $this->getModuleHandler()->useShared($shared_name);
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
        $random = new Ilib_RandomKeyGenerator();
        return $random->generate($length);
    }

    function getIntranet()
    {
    	return $this->intranet;
    }

    function getSetting()
    {
        if (is_object($this->user)) {
            $user_id = $this->user->getId();
        } else {
            $user_id = 0;
        }
        return new Intraface_Setting($this->intranet->getId(), $user_id);
        //return $this->setting;
    }
}