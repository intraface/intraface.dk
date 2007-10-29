<?php
require 'config.local.php';

set_include_path(INTRAFACE_PATH_INCLUDE);

require 'Intraface/Auth.php';
require 'Intraface/User.php';

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

    /*
    function __construct()
    {
        $this->auth = new Auth(md5(session_id()));
        if ($this->auth->isLoggedIn()) {
            $this->user = new User($this->auth->isLoggedIn());
        }
        if (!$this->user->setIntranetId(1)){}
    }
    */

    function login($user, $password)
    {
        $credentials = array('lsolesen' => 'klani');

        if (array_key_exists($user, $credentials)) {
            $this->is_logged_in = true;
        }

        //$this->auth->login($user, $password);
        //return ($this->user = new User($this->auth->isLoggedIn()));
    }

    function isLoggedIn()
    {
        return $this->is_logged_in;
        //return ($this->user->hasModuleAccess('intranetmaintenance') > 1);
    }
}

define('ERROR_LOG', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/log/error.log');
define('ERROR_LOG_UNIQUE', 'c:/Users/Lars Olesen/workspace/intraface/Intraface/log/unique-error.log');
define('PATH_WWW', 'http://');

$application = new Intraface_Tools_Controller_Root();

$application->registry->registerConstructor('errorlist', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_ErrorList(ERROR_LOG, ERROR_LOG_UNIQUE);'
));

$application->registry->registerConstructor('database', create_function(
  '$className, $args, $registry',
  'return MDB2::singleton("mysql://root:@localhost/intraface");'
));

$application->registry->registerConstructor('db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql;'
));

$application->dispatch();