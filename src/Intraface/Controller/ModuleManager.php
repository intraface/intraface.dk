<?php
ini_set('memory_limit', '32M');

class Intraface_Controller_ModuleManager extends k_Component
{
    protected $registry;
    protected $channels;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
        $this->channels = array(
            'pear.php.net'          => 'PEAR'
        );
    }

    function renderHtml()
    {
        $pear_user_config = '/etc/pear/pear.conf';

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

        $cmdopts = array();
        $opts    = array();
        $params  = array();
        if (isset($_GET['command']) && !is_null($_GET['command'])) {
            $command = $_GET['command'];
        } else {
            $command = 'list';
        }

        //$ui->outputBegin($command);

        // Handle some different Commands
        ob_start();

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
        $output = ob_get_contents();
        ob_end_clean();
        return $output;

        //$ui->outputEnd($command);
    }
}