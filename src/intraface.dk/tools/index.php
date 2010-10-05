<?php
require '../config.local.php';
require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
require_once 'bucket.inc.php';

set_error_handler('k_exceptions_error_handler');

class MyIdentityLoader extends k_BasicHttpIdentityLoader {
  function selectUser($username, $password) {
    $users = array(
      'sune@intraface.dk' => '7f5c04fb811783c71d951302e3314d62',
      'lars@intraface.dk' => 'e9127ee5efd3a78a5837f22a5bc4ef10'
    );
    if (isset($users[$username]) && $users[$username] == md5($password)) {
      return new k_AuthenticatedUser($username);
    }
  }
}

require_once 'MDB2.php';

class ToolsFactory
{
    public $db_dsn;
    public $error_log;

    function new_MDB2_Driver_Common($db)
    {
        $db = MDB2::singleton($this->db_dsn, array("persistent" => true));
        if (PEAR::isError($db)) {
            throw new Exception($db->getMessage());
        }
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $db->setOption("portability", MDB2_PORTABILITY_NONE);
        return $db;
    }

    function new_DB_Sql()
    {
        return new DB_Sql;
    }

    function new_Translation2_Admin()
    {
        $driver = "mdb2";
        $options = array(
            "hostspec" => DB_HOST,
            "database" => DB_NAME,
            "phptype" => "mysql",
            "username" => DB_USER,
            "password" => DB_PASS
        );
        $params = array(
            "langs_avail_table" => "core_translation_langs",
            "strings_default_table" => "core_translation_i18n"
        );

        $translation = Translation2_Admin::factory($driver, $options, $params);
        if (PEAR::isError($translation)) {
            exit($translation->getMessage());
        }
        return $translation;

    }

    function new_Translation2()
    {
        $driver = "mdb2";
        $options = array(
            "hostspec" => DB_HOST,
            "database" => DB_NAME,
            "phptype" => "mysql",
            "username" => DB_USER,
            "password" => DB_PASS
        );
        $params = array(
            "langs_avail_table" => "core_translation_langs",
            "strings_default_table" => "core_translation_i18n"
        );

        $translation = Translation2::factory($driver, $options, $params);
        if (PEAR::isError($translation)) {
            throw new Exception($translation->getMessage());
        }
        $translation->setLang("dk");
        return $translation;
    }

    function new_k_TemplateFactory($c)
    {
        return new Tools_TemplateFactory(null);
    }

    function new_Ilib_ErrorHandler_Observer_File_ErrorList()
    {
        return new Ilib_ErrorHandler_Observer_File_ErrorList($this->error_log);
    }
}


class Tools_TemplateFactory extends k_DefaultTemplateFactory
{
    function create($filename)
    {
        $filename = $filename . '.tpl.php';
        $__template_filename__ = k_search_include_path($filename);
        if (!is_file($__template_filename__)) {
            throw new Exception("Failed opening '".$filename."' for inclusion. (include_path=".ini_get('include_path').")");
        }
        return new k_Template($__template_filename__);
    }
}


function create_container() {
  $factory = new ToolsFactory();
  $container = new bucket_Container($factory);
  $factory->db_dsn = 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME;
  $factory->error_log = ERROR_LOG;
  return $container;
}


if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
  k()
      ->setIdentityLoader(new MyIdentityLoader())
      ->setComponentCreator(new k_InjectorAdapter(create_container()))
      ->run('Intraface_Tools_Controller_Root')->out();
}

