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

if (!function_exists('__')) {
    function __($phrase) {
        return $phrase;
    }
}

if (!function_exists('t')) {
    function t($phrase) {
        return $phrase;
    }
}


if (!function_exists('url')) {
    function url($url) {
        return PATH_WWW . $url;
    }
}

if (!function_exists('e')) {
    function e($text) {
        return htmlentities($text);
    }
}

require_once dirname(__FILE__) . '/common.php';

// error handling
set_error_handler('intrafaceFrontendErrorhandler', ERROR_HANDLE_LEVEL);
set_exception_handler('intrafaceFrontendExceptionhandler');

ob_start(); // ob_gzhandler()
session_start();

//$auth = new Intraface_Auth(session_id());
$auth = $bucket->get('Intraface_Auth');

if (!$auth->hasIdentity()) {
    $auth->toLogin();
}

$user = $auth->getIdentity($bucket->get('MDB2'));
$intranet = $user->getActiveIntranet();
// @todo why are we setting the id?
$user->setIntranetId($intranet->get('id'));

if ($intranet->getId() == 0) {
    trigger_error('no active intranet_id', E_USER_ERROR);
}
//$setting = new Intraface_Setting($kernel->intranet->get('id'), $kernel->user->get('id'));
$setting = $user->getSetting();
$language = $setting->get('user', 'language');

// makes intranet_id accessable in Doctrine
Intraface_Doctrine_Intranet::singleton($intranet->getId());


//$kernel = new Intraface_Kernel(session_id());
$kernel = $bucket->get('Intraface_Kernel');
$kernel->user = $user;
// $kernel->intranet = new Intraface_Intranet($intranet_id);
$kernel->intranet = $intranet;
$kernel->setting = $setting;

// @todo starting up a new bucket - should not be neccessary
//       it has to be refactored. It is Page which creates problems
//       as it needs a setup kernel.
$config = new Intraface_Config;
//$config->language = $language;
$config->kernel = $kernel;

$bucket = new bucket_Container(new Intraface_Factory($config));
$translation = $bucket->get('Translation2');
// set primary language
$set_language = $translation->setLang($language);

if (PEAR::isError($set_language)) {
    trigger_error($set_language->getMessage(), E_USER_ERROR);
}

/*
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
*/
$kernel->translation = $translation;

/*
// @todo CHANGE THIS TO BUCKET INSTEAD
$dependency = new Intraface_Dependency();
$dependency->whenCreating('Intraface_modules_product_Gateway')->forVariable('kernel')->willUse($kernel);
*/