<?php
/**
 * package.xml generation script
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @version @package-version@
 */

$version = '1.16.0';
$stability = 'stable';
$notes = '
* Added short description to product attribute group. Corrected translation pageId.
';
$web_dir = 'src/intraface.dk';

// @todo make sure that there is not created an intraface.dk/intraface.dk on the server
// @todo make sure that there is not created an install dir on the server.
$ignore = array(
            'intraface.dk/config.local.php',
            'intraface.dk/config.local.default.php',
            'intraface.dk/demo/config.local.php',
            'intraface.dk/demo/config.local.example.php',
            'intraface.dk/install/',
            'intraface.dk/install/reset-staging-server.php',
            '.svn/',
            '.settings/'
            );

function getFilelist($dir) {
    global $rFiles;
    $files = glob($dir.'/*');
    foreach ($files as $f) {
        if (is_dir($f)) { getFileList($f); continue; }
        $rFiles[] = $f;
    }
}

getFilelist($web_dir);

$web_files = $rFiles;

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(
    array(
        'baseinstalldir'    => '/',
        'filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__).'/src',
        'packagefile'       => 'package.xml',
        'ignore'            => $ignore,
        'dir_roles'        => array(
              'intraface.dk' => 'www'
        ),
        'exceptions' => array(
              'intraface.dk/*.*' => 'www'
        ),
        'simpleoutput'      => true,
        'addhiddenfiles' => true
    )
);

$pfm->setPackage('Intraface');
$pfm->setSummary('Intraface');
$pfm->setDescription('Intraface');
$pfm->setUri('http://localhost/');
$pfm->setLicense('LGPL License', 'http://www.gnu.org/licenses/lgpl.html');
$pfm->addMaintainer('lead', 'lsolesen', 'Lars Olesen', 'lars@legestue.net');
$pfm->addMaintainer('lead', 'sune.t.jensen', 'Sune Jensen', 'sj@sunet.dk');

$pfm->setPackageType('php');

$pfm->setAPIVersion($version);
$pfm->setReleaseVersion($version);
$pfm->setAPIStability($stability);
$pfm->setReleaseStability($stability);
$pfm->setNotes($notes);
$pfm->addRelease();

$pfm->resetUsesRole();
$pfm->addUsesRole('web', 'Role_Web', 'pearified.com');
$pfm->addPackageDepWithChannel('required', 'Role_Web', 'pearified.com', '1.1.1');

// $pfm->addGlobalReplacement('package-info', '@package-version@', 'version');
$pfm->addReplacement('intraface.php', 'pear-config', '@php-dir@', 'php_dir');
$pfm->addReplacement('intraface.php', 'pear-config', '@web-dir@', 'web_dir');
$pfm->addReplacement('intraface.php', 'pear-config', '@data-dir@', 'data_dir');

$pfm->clearDeps();
$pfm->setPhpDep('5.2.0');
$pfm->setPearinstallerDep('1.8.1');

// installer
$pfm->addPackageDepWithChannel('required', 'Config', 'pear.php.net', '1.10.11');
$pfm->addPackageDepWithChannel('required', 'MDB2_Schema', 'pear.php.net', '0.8.5');

// Kernel
$pfm->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.4.1');
$pfm->addPackageDepWithChannel('required', 'MDB2_Driver_mysql', 'pear.php.net', '1.4.1');
$pfm->addPackageDepWithChannel('required', 'Translation2', 'pear.php.net', '2.0.0');
$pfm->addPackageDepWithChannel('required', 'Translation2_Decorator_LogMissingTranslation', 'public.intraface.dk', '0.1.3');
$pfm->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.11.4');
$pfm->addPackageDepWithChannel('required', 'Validate', 'pear.php.net', '0.8.2');
$pfm->addPackageDepWithChannel('required', 'Net_IDNA', 'pear.php.net', '0.7.2');
$pfm->addPackageDepWithChannel('required', 'HTTP_Upload', 'pear.php.net', '0.9.1');
$pfm->addPackageDepWithChannel('required', 'Image_Transform', 'pear.php.net', '0.9.1');
$pfm->addPackageDepWithChannel('required', 'ErrorHandler', 'public.intraface.dk', '0.2.6');
$pfm->addPackageDepWithChannel('required', 'MDB2_Debug_ExplainQueries', 'public.intraface.dk', '0.1.1');
$pfm->addPackageDepWithChannel('required', 'File', 'pear.php.net', '1.3.0');
$pfm->addPackageDepWithChannel('required', 'Ilib_RandomKeyGenerator', 'public.intraface.dk', '0.3.0');
$pfm->addPackageDepWithChannel('required', 'Ilib_Position', 'public.intraface.dk', '0.3.0');
$pfm->addPackageDepWithChannel('required', 'phemto', 'public.intraface.dk', '0.1.0');


// Doctrine // remember to move it to correct installed dir afterwards
$pfm->addPackageDepWithChannel('required', 'Doctrine', 'pear.phpdoctrine.org', '1.1.1');
$pfm->addPackageDepWithChannel('required', 'Doctrine_Validator_Nohtml', 'public.intraface.dk', '0.1.0');
$pfm->addPackageDepWithChannel('required', 'Doctrine_Validator_Greaterthan', 'public.intraface.dk', '0.1.0');
$pfm->addPackageDepWithChannel('required', 'Doctrine_Template_Positionable', 'public.intraface.dk', '0.2.0');

// Ilib
$pfm->addPackageDepWithChannel('required', 'Ilib_Category', 'public.intraface.dk', '0.1.4');
$pfm->addPackageDepWithChannel('required', 'Ilib_DBQuery', 'public.intraface.dk', '0.1.3');
$pfm->addPackageDepWithChannel('required', 'Ilib_Error', 'public.intraface.dk', '0.0.1');
$pfm->addPackageDepWithChannel('required', 'Ilib_Redirect', 'public.intraface.dk', '0.2.1');
$pfm->addPackageDepWithChannel('required', 'Ilib_FileImport', 'public.intraface.dk', '0.1.0');
$pfm->addPackageDepWithChannel('required', 'Ilib_Validator', 'public.intraface.dk', '0.0.2');
$pfm->addPackageDepWithChannel('required', 'Ilib_ClassLoader', 'public.intraface.dk', '0.1.1');
$pfm->addPackageDepWithChannel('required', 'Ilib_Variable', 'public.intraface.dk', '0.2.2');

// other intraface 3_Party packages
$pfm->addPackageDepWithChannel('required', 'DB_Sql', 'public.intraface.dk', '0.0.1');

// XMLRPC
$pfm->addPackageDepWithChannel('required', 'XML_RPC2', 'pear.php.net', '1.0.5');
// Bug fix for PEAR XML_RPC2 version 1.0.2
// $pfm->addPackageDepWithChannel('required', 'XML_RPC2', 'public.intraface.dk', '0.0.1');
// Yet another bug fix for PEAR XML_RPC2 version 1.0.2
// $pfm->addPackageDepWithChannel('required', 'XML_RPC2_Backend_Php_ServerFixedEncodingObject', 'public.intraface.dk', '0.0.1');

// filehandler
$pfm->addPackageDepWithChannel('required', 'MIME_Type', 'pear.php.net', '1.0.0');
$pfm->addPackageDepWithChannel('required', 'System_Command', 'pear.php.net', '1.0.6');
//$pfm->addPackageDepWithChannel('required', 'Ilib_Filehandler', 'public.intraface.dk', '0.1.0');
//$pfm->addPackageDepWithChannel('required', 'Ilib_Keyword', 'public.intraface.dk', '0.1.0');

// email
$pfm->addPackageDepWithChannel('required', 'phpmailer', 'public.intraface.dk', '1.73.1');

// cms
$pfm->addPackageDepWithChannel('required', 'XML_Util', 'pear.php.net', '1.2.0');
$pfm->addPackageDepWithChannel('required', 'XML_Serializer', 'pear.php.net', '0.20.0');
$pfm->addPackageDepWithChannel('required', 'HTMLPurifier', 'htmlpurifier.org', '3.1.0');
$pfm->addPackageDepWithChannel('required', 'Text_Wiki', 'pear.php.net', '1.2.0');
$pfm->addPackageDepWithChannel('required', 'Markdown', 'pear.michelf.com', '1.0.1m');

$pfm->addPackageDepWithChannel('required', 'SmartyPants', 'pear.michelf.com', '1.5.1oo2');
$pfm->addPackageDepWithChannel('required', 'phpFlickr', 'public.intraface.dk', '1.6.1');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_CMS_HTML', 'public.intraface.dk', '0.2.0');

// debtor
$pfm->addPackageDepWithChannel('required', 'Document_Cpdf', 'public.intraface.dk', '0.0.2');
$pfm->addPackageDepWithChannel('required', 'Console_Table', 'pear.php.net', '1.1.2');

// contact
$pfm->addPackageDepWithChannel('required', 'Services_Eniro', 'public.intraface.dk', '1.0.0');
$pfm->addPackageDepWithChannel('required', 'Contact_Vcard_Build', 'pear.php.net', '1.1.1');
$pfm->addPackageDepWithChannel('required', 'Date', 'pear.php.net', '1.4.7');

// onlinepayment
$pfm->addPackageDepWithChannel('required', 'Payment_Quickpay', 'public.intraface.dk', '1.18.3');
$pfm->addPackageDepWithChannel('required', 'Validate_Finance_CreditCard', 'pear.php.net', '0.5.2');


// accounting
$pfm->addPackageDepWithChannel('required', 'OLE', 'pear.php.net', '0.6.1');
$pfm->addPackageDepWithChannel('required', 'Spreadsheet_Excel_Writer', 'pear.php.net', '0.9.1');

// modulepackage
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Debtor_XMLRPC', 'public.intraface.dk', '0.1.0');
$pfm->addPackageDepWithChannel('required', 'Ilib_Payment_Authorize_Provider_Testing', 'public.intraface.dk', '0.1.3');

// shop
$pfm->addPackageDepWithChannel('required', 'Ilib_Countries', 'public.intraface.dk', '1.0.0');

// demo
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_CMS', 'public.intraface.dk', '0.1.6');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_CMS_Client_XMLRPC', 'public.intraface.dk', '0.2.0');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_CMS_Controller', 'public.intraface.dk', '1.0.0');

$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Admin_Client_XMLRPC', 'public.intraface.dk', '0.1.0');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_OnlinePayment_Client_XMLRPC', 'public.intraface.dk', '1.0.0');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_OnlinePayment_Controller', 'public.intraface.dk', '0.5.2');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Shop', 'public.intraface.dk', '0.5.0');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Shop_Client_XMLRPC', 'public.intraface.dk', '1.0.3');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Shop_Controller', 'public.intraface.dk', '1.0.1');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Newsletter_Client_XMLRPC', 'public.intraface.dk', '1.0.0');
$pfm->addPackageDepWithChannel('required', 'IntrafacePublic_Newsletter_Controller', 'public.intraface.dk', '1.1.0');
$pfm->addPackageDepWithChannel('required', 'konstrukt', 'public.intraface.dk', '0.4.0');
$pfm->addPackageDepWithChannel('required', 'ilib_recursive_array_map', 'public.intraface.dk', '0.1.0');

// tools
$pfm->addPackageDepWithChannel('required', 'Ilib_SimpleLogin', 'public.intraface.dk', '1.0.0');
$pfm->addPackageDepWithChannel('required', 'Ilib_ErrorHandler_Observer_File_ErrorList', 'public.intraface.dk', '1.0.1');
$pfm->addPackageDepWithChannel('required', 'Translation2_Frontend', 'public.intraface.dk', '1.0.0');

foreach ($ignore AS $file) {
    // $pfm->addIgnoreToRelease($file);
}

/**
 * @todo: path_include_path: what to set it to?
 */

$post_install_script = $pfm->initPostinstallScript('intraface.php');
$post_install_script->addParamGroup('setup',
    array($post_install_script->getParam('db_user', 'User', 'string', 'root'),
          $post_install_script->getParam('db_pass', 'Password', 'string', ''),
          $post_install_script->getParam('db_host', 'Host', 'string', 'localhost'),
          $post_install_script->getParam('db_name', 'Database', 'string', 'intraface'),
          $post_install_script->getParam('net_scheme', 'Net scheme', 'string', 'http://'),
          $post_install_script->getParam('net_host', 'Net host', 'string', 'localhost'),
          $post_install_script->getParam('net_directory', 'Net directory', 'string', '/'),
          $post_install_script->getParam('path_root', 'Root path', 'string', '/home/intraface/'),
          $post_install_script->getParam('path_include_path', 'Include path', 'string', ''),
          $post_install_script->getParam('path_upload', 'Upload path', 'string', '/home/intraface/upload/'),
          $post_install_script->getParam('path_cache', 'Cache path', 'string', '/home/intraface/cache'),
          $post_install_script->getParam('connection_internet', 'Connection to intranet', 'boolean', true),
          $post_install_script->getParam('server_status', 'Server status', 'string', 'PRODUCTION'),
          $post_install_script->getParam('error_handle_level', 'Error handle error ', 'integer', E_ALL),
          $post_install_script->getParam('error_level_continue_script', 'Error level continue script', 'integer', E_USER_NOTICE ^ E_NOTICE),
          $post_install_script->getParam('error_report_email', 'Error report email', 'string', 'support@intraface.dk'),
          $post_install_script->getParam('error_log', 'Error log', 'string', 'log/error.log'),
          $post_install_script->getParam('timezone', 'Timezone', 'string', 'Europe/Copenhagen'),
          $post_install_script->getParam('country_local', 'Country local', 'string', 'da_DK'),
          $post_install_script->getParam('intraface_intranetmaintenance_intranet_private_key', 'Private key', 'string', ''),
          $post_install_script->getParam('intraface_onlinepayment_provider', 'Online payment provider', 'string', 'Quickpay'),
          $post_install_script->getParam('intraface_onlinepayment_merchant', 'Online payment merchant number', 'string', ''),
          $post_install_script->getParam('intraface_onlinepayment_md5secret', 'Online payment md5secret', 'string', '')
              ),
    '');

$pfm->addPostInstallTask($post_install_script, 'intraface.php');

foreach ($web_files AS $file) {
    $src_file = substr($file, 4);
    $formatted_file = substr($file, strlen($web_dir . '/'));
    if (in_array($src_file, $ignore)) continue;
    $pfm->addInstallAs($src_file, $formatted_file);
}

$pfm->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $res = $pfm->writePackageFile();
    if(PEAR::isError($res)) {
        echo $res->toString()."\n";
    }
    
    if ($res) {
        exit("Package file written\n");
    }
} else {
    $res = $pfm->debugPackageFile();
    
    if(PEAR::isError($res)) {
        echo $res->toString()."\n";
    }
    
    
}
?>