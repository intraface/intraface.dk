<?php
/**
 * Put this file in a web-accessible directory as index.php (or similar)
 * and point your webbrowser to it.
 */

// $pear_dir must point to a valid PEAR install (=contains PEAR.php)
//$pear_dir = '/usr/share/php'; // default of install

// OPTIONAL: If you have a config file at a non-standard location,
// uncomment and supply it here:
$pear_user_config = '/etc/pear/pear.conf';

// OPTIONAL: If you have protected this webfrontend with a password in a
// custom way, then uncomment to disable the 'not protected' warning:
//$pear_frontweb_protected = true;


/***********************************************************
 * Following code tests $pear_dir and loads the webfrontend:
 */
/*
if (!file_exists($pear_dir.'/PEAR.php')) {
    trigger_error('No PEAR.php in supplied PEAR directory: '.$pear_dir,
                    E_USER_ERROR);
}
ini_set('include_path', $pear_dir);
*/
//require_once('PEAR.php');

// Include WebInstaller
//putenv('PHP_PEAR_INSTALL_DIR='.$pear_dir); // needed if unexisting config
//define('PEAR_Frontend_Web',1);
//@session_start();

/**
 * base frontend class
 */
require_once 'PEAR.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Command.php';

// for the open_basedir prisoners, don't allow PEAR to search for a temp dir (would use /tmp), see bug #13167
//putenv('TMPDIR='.dirname(__FILE__).'/temp');

/*
// set $pear_user_config if it isn't set yet
// finds an existing file, or proposes the default location
if (!isset($pear_user_config) || $pear_user_config == '') {
    if (OS_WINDOWS) {
        $conf_name = 'pear.ini';
    } else {
        $conf_name = 'pear.conf';
    }

    // default backup config: the one from the installer (if available).
    $install_config = '@pear_install_config@'; // filled in on install
    // TODO: doesn't work yet ! There is no way to find the system config
    if (file_exists($install_config)) {
        $pear_user_config = $install_config;
    } else {

        // find other config file location
        $default_config_dirs = array(
            substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR)), // strip eg PEAR from .../example/PEAR(/pearfrontendweb.php)
            dirname($_SERVER['SCRIPT_FILENAME']),
            PEAR_CONFIG_SYSCONFDIR,
                    );
        // set the default: __FILE__ without PEAR/
        $pear_user_config = $default_config_dirs[0].DIRECTORY_SEPARATOR.$conf_name;

        $found = false;
        foreach ($default_config_dirs as $confdir) {
            if (file_exists($confdir.DIRECTORY_SEPARATOR.$conf_name)) {
                $pear_user_config = $confdir.DIRECTORY_SEPARATOR.$conf_name;
                $found = true;
                break;
            }
        }

        if (!$found) {
            print('<p><b>Warning:</b> Can not find config file, please specify the $pear_user_config variable in '.$_SERVER['PHP_SELF'].'</p>');
        }
    }
    unset($conf_name, $default_config_dirs, $confdir);
}
*/
require_once 'PEAR/Registry.php';
require_once 'PEAR/Config.php';

// moving this here allows startup messages and errors to work properly
PEAR_Frontend::setFrontendClass('PEAR_Frontend_Web');
// Init PEAR Installer Code and WebFrontend
$GLOBALS['_PEAR_Frontend_Web_config'] = &PEAR_Config::singleton($pear_user_config, '');
$config = &$GLOBALS['_PEAR_Frontend_Web_config'];
if (PEAR::isError($config)) {
    die('<b>Error:</b> '.$config->getMessage());
}

$ui = &PEAR_Command::getFrontendObject();
if (PEAR::isError($ui)) {
    die('<b>Error:</b> '.$ui->getMessage());
}
$ui->setConfig($config);

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));


// Cient requests an Image/Stylesheet/Javascript
// outputFrontendFile() does exit()
/*
if (isset($_GET["css"])) {
    $ui->outputFrontendFile($_GET["css"], 'css');
}
if (isset($_GET["js"])) {
    $ui->outputFrontendFile($_GET["js"], 'js');
}

if (isset($_GET["img"])) {
    $ui->outputFrontendFile($_GET["img"], 'image');
}


$verbose = $config->get("verbose");
$cmdopts = array();
$opts    = array();
$params  = array();
*/
/*
// create $pear_user_config if it doesn't exit yet
if (!file_exists($pear_user_config)) {
    // I think PEAR_Frontend_Web is running for the first time!
    // Create config and install it properly ...
    $ui->outputBegin(null);
    print('<h3>Preparing PEAR_Frontend_Web for its first time use...</h3>');

    // find pear_dir:
    if (!isset($pear_dir) || !file_exists($pear_dir)) {
        // __FILE__ is eg .../example/PEAR/pearfrontendweb.php
        $pear_dir = dirname(__FILE__); // eg .../example/PEAR
    }
    if (substr($pear_dir, -1) == DIRECTORY_SEPARATOR) {
        $pear_dir = substr($pear_dir, 0, -1); // strip trailing /
    }
    // extract base_dir from pear_dir
    $dir = substr($pear_dir, 0, strrpos($pear_dir, DIRECTORY_SEPARATOR)); // eg .../example

    $dir .= DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) {
        PEAR::raiseError('Can not find a base installation directory of PEAR ('.$dir.' doesn\'t work), so we can\'t create a config for it. Please supply it in the variable \'$pear_dir\'. The $pear_dir must have at least the subdirectory PEAR/ and be writable by this frontend.');
        die();
    }

    print('Saving config file ('.$pear_user_config.')...');
    // First of all set some config-vars:
    // Tries to be compatible with go-pear
    if (!isset($pear_dir)) {
        $pear_dir = $dir.'PEAR'; // default (go-pear compatible)
    }
    $cmd = PEAR_Command::factory('config-set', $config);
    $ok = $cmd->run('config-set', array(), array('php_dir',  $pear_dir));
    $ok = $cmd->run('config-set', array(), array('doc_dir',  $pear_dir.'/docs'));
    $ok = $cmd->run('config-set', array(), array('ext_dir',  $dir.'ext'));
    $ok = $cmd->run('config-set', array(), array('bin_dir',  $dir.'bin'));
    $ok = $cmd->run('config-set', array(), array('data_dir', $pear_dir.'/data'));
    $ok = $cmd->run('config-set', array(), array('test_dir', $pear_dir.'/test'));
    $ok = $cmd->run('config-set', array(), array('temp_dir', $dir.'temp'));
    $ok = $cmd->run('config-set', array(), array('download_dir', $dir.'temp/download'));
    $ok = $cmd->run('config-set', array(), array('cache_dir', $pear_dir.'/cache'));
    $ok = $cmd->run('config-set', array(), array('cache_ttl', 300));
    $ok = $cmd->run('config-set', array(), array('default_channel', 'pear.php.net'));
    $ok = $cmd->run('config-set', array(), array('preferred_mirror', 'pear.php.net'));

    print('Checking package registry...');
    // Register packages
    $packages = array(
                                'Archive_Tar',
                                'Console_Getopt',
                                'HTML_Template_IT',
                                'PEAR',
                                'PEAR_Frontend_Web',
                                'Structures_Graph'
                        );
    $reg = &$config->getRegistry();
    if (!file_exists($pear_dir.'/.registry')) {
        PEAR::raiseError('Directory "'.$pear_dir.'/.registry" does not exist. please check your installation');
    }

    foreach($packages as $pkg) {
        $info = $reg->packageInfo($pkg);
        foreach($info['filelist'] as $fileName => $fileInfo) {
            if($fileInfo['role'] == "php") {
                $info['filelist'][$fileName]['installed_as'] =
                    str_replace('{dir}',$dir, $fileInfo['installed_as']);
            }
        }
        $reg->updatePackage($pkg, $info, false);
    }

    print('<p><em>PEAR_Frontend_Web configured succesfully !</em></p>');
    $msg = sprintf('<p><a href="%s">Click here to continue</a></p>',
                    $_SERVER['PHP_SELF']);
    print($msg);
    $ui->outputEnd(null);
    die();
}
*/
/*
// Check _isProtected() override (disables the 'not protected' warning)
if (isset($pear_frontweb_protected) && $pear_frontweb_protected === true) {
    $GLOBALS['_PEAR_Frontend_Web_protected'] = true;
}

$cache_dir = $config->get('cache_dir');
if (!is_dir($cache_dir)) {
    include_once 'System.php';
    if (!System::mkDir('-p', $cache_dir)) {
        PEAR::raiseError('Directory "'.$cache_dir.'" does not exist and cannot be created. Please check your installation');
    }
}
*/

if (isset($_GET['command']) && !is_null($_GET['command'])) {
    $command = $_GET['command'];
} else {
    $command = 'list';
}

// Prepare and begin output
$ui->outputBegin($command);

// Handle some different Commands
    switch ($command) {
        case 'install':
        case 'uninstall':
        case 'upgrade':
            if ($_GET['command'] == 'install') {
                // also install dependencies
                $opts['onlyreqdeps'] = true;
                if (isset($_GET['force']) && $_GET['force'] == 'on') {
                    $opts['force'] = true;
                }
            }

            if (strpos($_GET['pkg'], '\\\\') !== false) {
                $_GET['pkg'] = stripslashes($_GET['pkg']);
            }
            $params = array($_GET["pkg"]);
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            $reg = &$config->getRegistry();
            PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
            $err = $reg->parsePackageName($_GET['pkg']);
            PEAR::staticPopErrorHandling(); // reset error handling

            if (!PEAR::isError($err)) {
                $ui->finishOutput('Back', array('link' => $_SERVER['PHP_SELF'].'?command=info&pkg='.$_GET['pkg'],
                    'text' => 'View package information'));
            }
            break;
        case 'run-scripts' :
            $params = array($_GET['pkg']);
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);
            break;
        case 'info':
        case 'remote-info':
            $reg = &$config->getRegistry();
            // we decide what it is:
            $pkg = $reg->parsePackageName($_GET['pkg']);
            if ($reg->packageExists($pkg['package'], $pkg['channel'])) {
                $command = 'info';
            } else {
                $command = 'remote-info';
            }

            $params = array(strtolower($_GET['pkg']));
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            break;
        case 'search':
            if (!isset($_POST['search']) || $_POST['search'] == '') {
                // unsubmited, show forms
                $ui->outputSearch();
            } else {
                if ($_POST['channel'] == 'all') {
                    $opts['allchannels'] = true;
                } else {
                    $opts['channel'] = $_POST['channel'];
                }
                $opts['channelinfo'] = true;

                // submited, do search
                switch ($_POST['search']) {
                    case 'name':
                        $params = array($_POST['input']);
                        break;
                    case 'description':
                        $params = array($_POST['input'], $_POST['input']);
                        break;
                    default:
                        PEAR::raiseError('Can\'t search for '.$_POST['search']);
                        break;
                }

                $cmd = PEAR_Command::factory($command, $config);
                $ok = $cmd->run($command, $opts, $params);
            }

            break;
        case 'config-show':
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            // if this code is reached, the config vars are submitted
            $set = PEAR_Command::factory('config-set', $config);
            foreach($GLOBALS['_PEAR_Frontend_Web_Config'] as $var => $value) {
                if ($var == 'Filename') {
                    continue; // I hate obscure bugs
                }
                if ($value != $config->get($var)) {
                    print('Saving '.$var.'... ');
                    $res = $set->run('config-set', $opts, array($var, $value));
                    $config->set($var, $value);
                }
            }
            print('<p><b>Config saved succesfully!</b></p>');

            $ui->finishOutput('Back', array('link' => $_SERVER['PHP_SELF'].'?command='.$command, 'text' => 'Back to the config'));
            break;
        case 'list-files':
            $params = array($_GET['pkg']);
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);
            break;
        case 'list-docs':
            if (!isset($_GET['pkg'])) {
                PEAR::raiseError('The webfrontend-command list-docs needs at least one \'pkg\' argument.');
                break;
            }

            require_once('PEAR/Frontend/Web/Docviewer.php');
            $reg = $config->getRegistry();
            $pkg = $reg->parsePackageName($_GET['pkg']);

            $docview = new PEAR_Frontend_Web_Docviewer($ui);
            $docview->outputListDocs($pkg['package'], $pkg['channel']);
            break;
        case 'doc-show':
            if (!isset($_GET['pkg']) || !isset($_GET['file'])) {
                PEAR::raiseError('The webfrontend-command list-docs needs one \'pkg\' and one \'file\' argument.');
                break;
            }

            require_once('PEAR/Frontend/Web/Docviewer.php');
            $reg = $config->getRegistry();
            $pkg = $reg->parsePackageName($_GET['pkg']);

            $docview = new PEAR_Frontend_Web_Docviewer($ui);
            $docview->outputDocShow($pkg['package'], $pkg['channel'], $_GET['file']);
            break;
        case 'list-all':
            // Deprecated, use 'list-categories' is used instead
            if (isset($_GET['chan']) && $_GET['chan'] != '') {
                $opts['channel'] = $_GET['chan'];
            }
            $opts['channelinfo'] = true;
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            break;
        case 'list-categories':
        case 'list-packages':
            if (isset($_GET['chan']) && $_GET['chan'] != '') {
                $opts['channel'] = $_GET['chan'];
            } else {
                // show 'table of contents' before all channel output
                $ui->outputTableOfChannels();

                $opts['allchannels'] = true;
            }
            if (isset($_GET['opt']) && $_GET['opt'] == 'packages') {
                $opts['packages'] = true;
            }
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            break;
        case 'list-category':
            if (isset($_GET['chan']) && $_GET['chan'] != '') {
                $opts['channel'] = $_GET['chan'];
            }
            $params = array($_GET['cat']);
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            break;
        case 'list':
            $opts['allchannels'] = true;
            $opts['channelinfo'] = true;
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            break;
        case 'list-upgrades':
            $opts['channelinfo'] = true;
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);
            $ui->outputUpgradeAll();

            break;
        case 'upgrade-all':
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            $ui->finishOutput('Back', array('link' => $_SERVER['PHP_SELF'].'?command=list',
                'text' => 'Click here to go back'));
            break;
        case 'channel-info':
            if (isset($_GET['chan']))
                $params[] = $_GET['chan'];
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            break;
        case 'channel-discover':
            if (isset($_GET['chan']) && $_GET['chan'] != '')
                $params[] = $_GET['chan'];
            $cmd = PEAR_Command::factory($command, $config);
            $ui->startSession();
            $ok = $cmd->run($command, $opts, $params);

            $ui->finishOutput('Channel Discovery', array('link' =>
                $_SERVER['PHP_SELF'] . '?command=channel-info&chan=' . urlencode($_GET['chan']),
                'text' => 'Click Here for ' . htmlspecialchars($_GET['chan']) . ' Information'));
            break;
        case 'channel-delete':
            if (isset($_GET["chan"]))
                $params[] = $_GET["chan"];
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            $ui->finishOutput('Delete Channel', array('link' =>
                $_SERVER['PHP_SELF'] . '?command=list-channels',
                'text' => 'Click here to list all channels'));
            break;
        case 'list-channels':
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            break;
        case 'channel-update':
            if (isset($_GET['chan'])) {
                $params = array($_GET['chan']);
            }
            $cmd = PEAR_Command::factory($command, $config);
            $ok = $cmd->run($command, $opts, $params);

            break;
        case 'update-channels':
            // update every channel manually,
            // fixes bug PEAR/#10275 (XML_RPC dependency)
            // will be fixed in next pear release
            $reg = &$config->getRegistry();
            $channels = $reg->getChannels();
            $command = 'channel-update';
            $cmd = PEAR_Command::factory($command, $config);

            $success = true;
            $ui->startSession();
            foreach ($channels as $channel) {
                if ($channel->getName() != '__uri') {
                    $success &= $cmd->run($command, $opts,
                                          array($channel->getName()));
                }
            }

            $ui->finishOutput('Update Channel List', array('link' =>
                $_SERVER['PHP_SELF'] . '?command=list-channels',
                'text' => 'Click here to list all channels'));
            break;
        default:
            $cmd = PEAR_Command::factory($command, $config);
            $res = $cmd->run($command, $opts, $params);

            break;
    }

//$ui->outputEnd($command);

?>
