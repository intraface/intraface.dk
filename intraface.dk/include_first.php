<?php
/**
 * This file is to be included on every page where user is logged in.
 *
 * @author Lars Olesen <lars@legestue.net>
 */
if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
    trigger_error('This file cannot be accessed directly', E_USER_ERROR);
}

$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.php';

if (!file_exists($config_file)) {
    die('The config.php file is missing. Please create it.');
}

require $config_file;

require_once dirname(__FILE__) . '/common.php';

function intrafaceFrontendErrorhandler($errno, $errstr, $errfile, $errline, $errcontext) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if(defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // From php.net "~ $a: Bits that are set in $a are not set, and vice versa." That means the observer is used on everything but ERROR_LEVEL_CONTINUE_SCRIPT
    } else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // See description of ~ above
    }
    return $errorhandler->handleError($errno, $errstr, $errfile, $errline, $errcontext);
}

function intrafaceFrontendExceptionhandler($e) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if(defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen);
    } else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User);
    }
    return $errorhandler->handleException($e);
}

set_error_handler('intrafaceFrontendErrorhandler', ERROR_HANDLE_LEVEL);
set_exception_handler('intrafaceFrontendExceptionhandler');

ob_start(); // ob_gzhandler()
session_start();

$auth = new Intraface_Auth(session_id());

if (!$auth->hasIdentity()) {
    $auth->toLogin();
}

$kernel = new Intraface_Kernel(session_id());

$kernel->user = $auth->getIdentity(MDB2::singleton(DB_DSN));

if (!$intranet_id = $kernel->user->getActiveIntranetId()) {
    trigger_error('no active intranet_id', E_USER_ERROR);
}

$kernel->intranet = new Intraface_Intranet($intranet_id);

// @todo why are we setting the id?
$kernel->user->setIntranetId($kernel->intranet->get('id'));
$kernel->setting = new Intraface_Setting($kernel->intranet->get('id'), $kernel->user->get('id'));

$language = $kernel->setting->get('user', 'language');

// makes intranet_id accessable in Doctrine
Intraface_Doctrine_Intranet::singleton($kernel->intranet->getId());

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

if (!function_exists('t')) {
  /**
   * This function is dynamically redefinable.
   * @see $GLOBALS['_global_function_callback_e']
   */
  function t($args) {
    $args = func_get_args();
    return call_user_func_array($GLOBALS['_global_function_callback_t'], $args);
  }
  if (!isset($GLOBALS['_global_function_callback_t'])) {
    $GLOBALS['_global_function_callback_t'] = NULL;
  }
}

$GLOBALS['_global_function_callback_t'] = 'intraface_t';

function intraface_t($string, $page = NULL)
{
    global $translation;
    if($page !== NULL) {
        return $translation->get($string, $page);
    } else {
        return $translation->get($string);
    }
}