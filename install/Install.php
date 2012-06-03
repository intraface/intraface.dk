<?php
class Intraface_Install
{
    /**
     * @var object database connection
     */
    private $db;

    /**
     * constructor. Checks if the script can be run. Connects to database.
     */
    function __construct()
    {
        if (!defined('SERVER_STATUS') OR SERVER_STATUS == 'PRODUCTION') {
            die('Can not be performed on PRODUCTION SERVER');
        } elseif (!empty($_SERVER['HTTP_HOST']) AND $_SERVER['HTTP_HOST'] == 'www.intraface.dk') {
            die('Can not be performed on www.intraface.dk');
        }

        $this->db = MDB2::singleton(DB_DSN);

        if (PEAR::isError($this->db)) {
            throw new Exception($this->db->getUserInfo());
        }
    }

    function dropDatabase()
    {
        $result = $this->db->query("SHOW TABLES FROM " . DB_NAME);
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        while ($line = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $drop = $this->db->exec('DROP TABLE ' . $line['Tables_in_'.DB_NAME]);
            if (PEAR::IsError($drop)) {
                throw new Exception($drop->getUserInfo());
            }
        }
        return true;
    }

    function createDatabaseSchema()
    {
        $sql_structure = file_get_contents(dirname(__FILE__) . '/database-structure.sql');
        $sql_arr = Intraface_Install::splitSql($sql_structure);

        foreach ($sql_arr as $sql) {
            if (empty($sql)) { continue; }
            $result = $this->db->exec($sql);
            if (PEAR::isError($result)) {
                throw new Exception($result->getUserInfo());
            }
        }

        $sql_structure = file_get_contents(dirname(__FILE__) . '/database-update.sql');
        $sql_arr = Intraface_Install::splitSql($sql_structure);

        foreach ($sql_arr as $sql) {
            if (empty($sql)) { continue; }
            $result = $this->db->exec($sql);
            if (PEAR::isError($result)) {
                throw new Exception($result->getUserInfo());
            }
        }
        return true;
    }

    function emptyDatabase()
    {
        $result = $this->db->query("SHOW TABLES FROM " . DB_NAME);
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        while ($line = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $truncate = $this->db->exec('TRUNCATE TABLE ' . $line['Tables_in_'.DB_NAME]);
            if (PEAR::IsError($truncate)) {
                throw new Exception($truncate->getUserInfo());
            }
        }
        return true;

    }

    function createStartingValues()
    {
        $sql_values = file_get_contents(dirname(__FILE__) . '/database-values.sql');
        $sql_arr = Intraface_Install::splitSql($sql_values);

        foreach ($sql_arr as $sql) {
            if (empty($sql)) { continue; }
            $result = $this->db->exec($sql);
            if (PEAR::isError($result)) {
                throw new Exception($result->getUserInfo());
            }
        }
        return true;
    }

    function resetServer()
    {
        /*
        if (!$this->dropDatabase()) {
            throw new Exception('could not drop database');
        }
        if (!$this->createDatabaseSchema()) {
            throw new Exception('could not create schema');
        }
        */

        if (!$this->emptyDatabase()) {
            throw new Exception('could not empty database');
        }

        if (!$this->createStartingValues()) {
            throw new Exception('could not create values');
        }

        $this->deleteUploadDirectory(PATH_UPLOAD);

        if (!file_exists(PATH_UPLOAD)) {
            mkdir(PATH_UPLOAD);
        }

        return true;

    }

    function deleteUploadDirectory($f)
    {
        if ( is_dir( $f ) ){
            foreach ( scandir( $f ) as $item ){
                if ( !strcmp( $item, '.' ) || !strcmp( $item, '..' ) )
                    continue;
                $this->deleteUploadDirectory( $f . "/" . $item );
            }
            rmdir( $f );
        } else{
            @unlink( $f );
        }
    }

    /**
     * grants access to given modules
     */
    public function grantModuleAccess($modules)
    {
        $this->registerModules();
        $modules = explode(',', $modules);

        require_once 'Intraface/modules/intranetmaintenance/IntranetMaintenance.php';
        // The moduleaccess only goes for intranet_id 1
        $intranet = new IntranetMaintenance(1);
        require_once 'Intraface/modules/intranetmaintenance/UserMaintenance.php';
        $user = new UserMaintenance(1);
        $user->setIntranetAccess(1);

        require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';
        foreach ($modules as $module_name) {
            $module = ModuleMaintenance::factory($module_name);

            if ($module->get('id') == 0) {
                throw new Exception('Invalid module '.$module_name);
            }
            $intranet->setModuleAccess($module->get('id'));
            $user->setModuleAccess($module->get('id'), 1);
            $sub_accesss = $module->get('sub_access');
            foreach ($sub_accesss as $sub_access) {
                $user->setSubAccess($module->get('id'), $sub_access['id'], 1);
            }
        }

        return true;

    }

    /**
     * login the user
     */
    function loginUser()
    {
        /* session_start(); */ // session_start is in reset_staging_server. Should only be one place.

        $adapter = new Intraface_Auth_User($this->db, session_id(), 'start@intraface.dk', 'startup');
        $auth = new Intraface_Auth(session_id());
        $user = $auth->authenticate($adapter);

        return $user;

    }

    /**
     * run helper functions
     */
    public function runHelperFunction($functions)
    {
        $functions = explode(',', $functions);

        // We create kernel so it can be used in the helper functions
        if (session_id() != '') {
            $kernel = new Intraface_Kernel(session_id());
        } else {
            $kernel = new Intraface_Kernel;
        }
        $kernel->user = new Intraface_User(1);
        $kernel->user->setIntranetId(1);
        $kernel->intranet = new Intraface_Intranet(1);
        $kernel->setting = new Intraface_Setting(1, 1);

        // adds the intranet_id to Doctrine!
        Intraface_Doctrine_Intranet::singleton(1);

        foreach ($functions AS $function) {
            $object_method = explode(':', trim($function));
            $object_method[0] = str_replace('/', '', $object_method[0]);
            $object_method[0] = str_replace('\\', '', $object_method[0]);

            require_once dirname(__FILE__) . '/Helper/'.$object_method[0].'.php';
            $object_name = 'Install_Helper_'.$object_method[0];
            $object = new $object_name($kernel, $this->db);
            $object->$object_method[1]();
        }
    }

    /**
     * register modules
     */
    private function registerModules()
    {
        require_once 'Intraface/modules/intranetmaintenance/ModuleMaintenance.php';
        $modulemaintenance = new ModuleMaintenance;
        $modulemaintenance->register();
    }

    /**
     * splits a mysql export into separate
     */
    static function splitSql($sql)
    {
        if (strpos($sql, "\r\n")) {
            $str_sep = "\r\n";
        } else {
            $str_sep = "\n";
        }
        if (substr($sql, 0, 2) == '--') {
            $sql = substr($sql, strpos($sql, $str_sep));
        }
        $sql = preg_replace($str_sep."/--[a-zA-Z0-9\/\:\`,. _-]*/", '', $sql);
        $parts = preg_split("/;( )*".$str_sep.'/', $sql);
        $parts = array_map('trim', $parts);
        return $parts;

    }
}
