<?php
require 'common.php';

ob_start(); // ob_gzhandler()
session_start();

require_once 'Intraface/Auth.php';

$auth = new Auth(session_id());

if (!$user_id = $auth->isLoggedIn()) {
	$auth->toLogin();
}

$kernel = new Kernel;
$kernel->user = new User($user_id);

if (!$intranet_id = $kernel->user->getActiveIntranetId('id')) {
	trigger_error('no active intranet_id', E_USER_ERROR);
}

$kernel->intranet = new Intranet($intranet_id);

// why are we setting the id?
$kernel->user->setIntranetId($kernel->intranet->get('id'));
$kernel->setting = new Setting($kernel->user->get('id'), $kernel->intranet->get('id'));

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

require_once('Translation2/Translation2.php');

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
?>
