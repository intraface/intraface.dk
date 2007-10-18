<?php
require 'k.php';
require 'config.local.php';

class User
{
    private $user;
    private $password;

    function checkRight($right)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return true;
    }

    function login($user, $password)
    {
        $this->user     = $user;
        $this->password = $password;
    }

    function isLoggedIn()
    {
        if ($this->user) {
            return true;
        }
        return false;
    }
}


$db = new DB_Sql;


$application = new Intraface_Tools_Controller_Root();

$application->registry->registerConstructor('errorlist', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_ErrorList($args);'
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