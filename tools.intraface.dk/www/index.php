<?php
require 'config.local.php';

set_include_path(INTRAFACE_PATH_INCLUDE);

require 'Intraface/Auth.php';
require 'Intraface/User.php';

define('INTRAFACE_TOOLS_DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);
define('DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);

require 'k.php';

class tools_ClassLoader extends k_classLoader
{
    static function pear_autoload($classname) {
        $filename = str_replace('_', '/', $classname).'.php';
        if (self::SearchIncludePath($filename)) {
        require_once($filename);
        }
    }
}

spl_autoload_register(Array('tools_ClassLoader', 'pear_autoload'));

class Tools_User
{
    private $auth;
    private $user;
    private $is_logged_in = false;

    function __construct($session = '')
    {
        $this->auth = new Auth(md5($session));
        if ($this->auth->isLoggedIn()) {
            $this->user = new User($this->auth->isLoggedIn());
            $this->user->setIntranetId(1);
        }
    }

    function login($user, $password)
    {
        $this->auth->login($user, $password);
        return ($this->user = new User($this->auth->isLoggedIn()));
    }

    function isLoggedIn()
    {
        if (!is_object($this->user)) {
            return false;
        }
        return $this->user->hasModuleAccess('intranetmaintenance');
    }
}

$application = new Intraface_Tools_Controller_Root();

$application->registry->registerConstructor('errorlist', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_ErrorList(ERROR_LOG, ERROR_LOG_UNIQUE);'
));

$application->registry->registerConstructor('database', create_function(
  '$className, $args, $registry',
  'return MDB2::singleton(INTRAFACE_TOOLS_DB_DSN);'
));

$application->registry->registerConstructor('db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql;'
));

$application->registry->registerConstructor('user', create_function(
  '$className, $args, $registry',
  'return new Tools_User($registry->get("session")->getSessionId());'
));

$application->dispatch();