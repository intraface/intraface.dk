<?php
// common settings
define('INTRAFACE_K2', true);

// HACK to have SET NAMES utf8
require_once dirname(__FILE__) . '/../config.local.php';
ini_set('include_path', PATH_INCLUDE_PATH);
require_once 'Intraface/Sql.php';
require_once dirname(__FILE__) . '/../common.php';
require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');


class Intraface_AuthenticatedUser extends k_AuthenticatedUser
{
    protected $language;
    function __construct($name, k_Language $lang)
    {
        $this->language = $lang;
        parent::__construct($name);
    }

    function language()
    {
        return $this->language;
    }
}


/*
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


class DanishLanguage implements k_Language {
  function name() {
    return 'Danish';
  }
  function isoCode() {
    return 'da';
  }
}

class EnglishLanguage implements k_Language {
  function name() {
    return 'English';
  }
  function isoCode() {
    return 'uk';
  }
}

class Intraface_LanguageLoader implements k_LanguageLoader {
  // @todo The language will often not be set on runtime, e.g. an
  //       intranet where the user can chose him or her own language?
  //       How could one accommodate for this?
  function load(k_Context $context) {
    require_once 'PEAR.php';
    require_once 'HTTP.php';

    if ($context->identity()->anonymous()) {
        $supported = array("da" => true, "en-US" => true);
        $language = HTTP::negotiateLanguage($supported);
        if (PEAR::isError($language)) {
          // fallback language in case of unable to negotiate
          return new DanishLanguage();
        }

        if ($language == 'en-US') {
            return new EnglishLanguage();
        }

    } elseif ($context->identity()->language() == 'da') {
        return new DanishLanguage();
    }

    return new EnglishLanguage();
  }
}

class k_Translation2Translator implements k_Translator {
  protected $translation2;
  function __construct($lang) {
    $factory = new Intraface_Factory;
    $this->translation2 = $factory->new_Translation2();
    $res = $this->translation2->setLang($lang);
    if (PEAR::isError($res)) {
        throw new Exception('Could not setLang()');
    }
  }

  function translate($phrase, k_Language $language = null) {
    // Translation2 groups translations with pageID(). This can
    // be accommodated like this
    if (is_array($phrase) && count($phrase) == 2) {
        return $this->translation2->get($phrase[0], $phrase[1]);
    }
    return $this->translation2->get($phrase, 'basic');
  }
}

class Intraface_TranslatorLoader implements k_TranslatorLoader {
  function load(k_Context $context) {
    return new k_Translation2Translator($context->language()->isoCode());
  }
}

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
  ->setLanguageLoader(new Intraface_LanguageLoader())->setTranslatorLoader(new Intraface_TranslatorLoader())
  ->run('Intraface_Controller_Index')
  ->out();
