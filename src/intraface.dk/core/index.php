<?php
require_once dirname(__FILE__) . '/../common.php';
ini_set('include_path', PATH_INCLUDE_PATH);

require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');
/*
require_once 'phemto.php';
function create_container() {
  $injector = new Phemto();
  // put application wiring here
  $template_dir = realpath(dirname(__FILE__) . '/../../../Intraface/modules/accounting/Controller/templates');
  $injector->whenCreating('TemplateFactory')->forVariable('template_dir')->willUse(new Value($template_dir));
  return $injector;
}

class TemplateFactory {
  protected $template_dir;
  function __construct($template_dir) {
    $this->template_dir = $template_dir;
  }
  function create() {
    $smarty = new k_Template($this->template_dir);
    return $smarty;
  }
}*/
session_start();
$kernel = new Intraface_Kernel(session_id());

$kernel->translation = $bucket->get('translation2');

$config->template_dir = realpath(dirname(__FILE__) . '/../../../Intraface/modules/accounting/Controller/templates');

/*
$language = $kernel->setting->get('user', 'language');

// set the parameters to connect to your db
$dbinfo = array(
    'hostspec' => DB_HOST,
    'database' => DB_NAME,
    'phptype'  => 'mysql',
    'username' => DB_USER,
    'password' => DB_PASS
);

if (!defined('LANGUAGE_TABLE_PREFIX')) {
    define('LANGUAGE_TABLE_PREFIX', 'core_translation_');
}

$params = array(
    'langs_avail_table' => LANGUAGE_TABLE_PREFIX.'langs',
    'strings_default_table' => LANGUAGE_TABLE_PREFIX.'i18n'
);

$translation = Translation2::factory('MDB2', $dbinfo, $params);
//always check for errors. In this examples, error checking is omitted
//to make the example concise.
if (PEAR::isError($translation)) {
    trigger_error('Could not start Translation ' . $translation->getMessage(), E_USER_ERROR);
}

// set primary language
$set_language = $translation->setLang($language);

if (PEAR::isError($set_language)) {
    trigger_error($set_language->getMessage(), E_USER_ERROR);
}

// set the group of strings you want to fetch from
// $translation->setPageID($page_id);

// add a Lang decorator to provide a fallback language
$translation = $translation->getDecorator('Lang');
$translation->setOption('fallbackLang', 'uk');
$translation = $translation->getDecorator('LogMissingTranslation');
require_once("ErrorHandler/Observer/File.php");
$translation->setOption('logger', array(new ErrorHandler_Observer_File(ERROR_LOG), 'update'));
$translation = $translation->getDecorator('DefaultText');

// %stringID% will be replaced with the stringID
// %pageID_url% will be replaced with the pageID
// %stringID_url% will replaced with a urlencoded stringID
// %url% will be replaced with the targeted url
//$this->translation->outputString = '%stringID% (%pageID_url%)'; //default: '%stringID%'
$translation->outputString = '%stringID%';
$translation->url = '';           //same as default
$translation->emptyPrefix  = '';  //default: empty string
$translation->emptyPostfix = '';  //default: empty string

$kernel->translation = $translation;
*/

class k_SessionIdentityLoader implements k_IdentityLoader {
  function load(k_Context $context) {
    if ($context->session('identity')) {
      return $context->session('identity');
    }
    return new k_Anonymous();
  }
}

class NotAuthorizedComponent extends k_Component {
  function dispatch() {
    // redirect to login-page
    return new k_TemporaryRedirect($this->url('/login', array('continue' => $this->requestUri())));
  }
}

$GLOBALS['kernel'] = $kernel;
//$GLOBALS['intranet'] = $kernel->intranet;
//$db = MDB2::singleton();
//$db->setCharset('utf8');

$GLOBALS['db'] = $db;
/*
class WireFactory {
    function __construct()
    {
    }

    function create()
    {
    	$registry = new k_Registry();
        $registry->registerConstructor('doctrine', create_function(
            '$className, $args, $registry',
            'return Doctrine_Manager::connection(DB_DSN);'
        ));
        $registry->registerConstructor('category_gateway', create_function(
          '$className, $args, $registry',
          'return new Intraface_modules_shop_Shop_Gateway;'
        ));

        $registry->registerConstructor('kernel', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["kernel"];'
        ));

        $registry->registerConstructor('intranet', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["intranet"];'
        ));

        $registry->registerConstructor('db', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["db"];'
        ));

        $registry->registerConstructor('page', create_function(
          '$className, $args, $registry',
          'return new Intraface_Page($registry->get("kernel"));'
        ));

        return $registry;
    }
}
*/

//$components = new k_InjectorAdapter(create_container());
$components = new k_InjectorAdapter($bucket);
$components->setImplementation('k_DefaultNotAuthorizedComponent', 'NotAuthorizedComponent');

k()
  // Use container for wiring of components
  ->setComponentCreator($components)
  // Enable file logging
  ->setLog(K2_LOG)
  // Uncomment the next line to enable in-browser debugging
  //->setDebug(K2_DEBUG)
  // Dispatch request
  ->setIdentityLoader(new k_SessionIdentityLoader())
  ->run('Intraface_Controller_Index')
  ->out();
