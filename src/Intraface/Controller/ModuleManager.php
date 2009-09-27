<?php
class Intraface_Controller_ModuleManager extends k_Component
{
    protected $registry;
    protected $channels;

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
        $this->channels = array(
            'pear.php.net'          => 'PEAR'
        );
    }

    function t($phrase)
    {
        return $phrase;
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/modulemanager.tpl.php');
        return $smarty->render($this);
    }

    function validate($req, &$input)
    {
        $this->validated        = true;

        //  default action is 'overview' unless paging through results,
        //  in which case default is 'list'
        $input->from            = $req->get('pageID');
        $input->totalItems      = $req->get('totalItems');
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete         = $req->get('frmDelete');
        $input->submitted       = $req->get('submitted');

        //  PEAR params
        $input->mode            = $req->get('mode');
        $input->channel         = $req->get('channel');
        $input->command         = $req->get('command');
        $input->pkg             = $this->restoreSlashes($req->get('pkg'));

        //  validate fields
        $aErrors = array();
        if ($input->submitted) {
            $aFields = array(
                'name' => 'Please, specify a name',
                'title' => 'Please, specify a title',
                'description' => 'Please, specify a description',
                'icon' => 'Please, specify the name of the icon-file'
            );
            foreach ($aFields as $field => $errorMsg) {
                if (empty($input->module->$field)) {
                    $aErrors[$field] = $errorMsg;
                }
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'moduleEdit.html';
            $this->validated = false;
        }
    }

    function restoreSlashes($str)
    {
        return str_replace('^', '/',$str);
    }

    function _cmd_doRequest(&$input, &$output)
    {
        $ok = ini_set('max_execution_time', 180);
        putenv('PHP_PEAR_INSTALL_DIR='.SGL_LIB_PEAR_DIR);

        #$useDHTML = true;
        define('PEAR_Frontend_Web',1);

        // Include needed files
        require_once 'PEAR/Registry.php';
        require_once 'PEAR/Config.php';
        require_once 'PEAR/Command.php';

        // Init PEAR Installer Code and WebFrontend
        #$config  = $GLOBALS['_PEAR_Frontend_Web_config'] = &PEAR_Config::singleton();
        $config  = $GLOBALS['_PEAR_Frontend_Web_config'] =
            &PEAR_Config::singleton($this->getPearConfigPath(), $this->getPearConfigPath());

        $config->set('default_channel', $input->channel);
        $config->set('preferred_state', 'devel');

        PEAR_Command::setFrontendType("WebSGL");
        $ui = &PEAR_Command::getFrontendObject();

        $verbose = $config->get("verbose");
        $cmdopts = array();
        $opts    = array();
        $params  = array();

        $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        #$dir = substr(dirname(__FILE__), 0, -strlen('PEAR/PEAR')); // strip PEAR/PEAR

        #$_ENV['TMPDIR'] = $_ENV['TEMP'] = $dir.'tmp';
        $_ENV['TMPDIR'] = $_ENV['TEMP'] = SGL_TMP_DIR;

        if ($input->command == 'sgl-install' || $input->command == 'sgl-install') {
            if (!is_writable(SGL_MOD_DIR)) {
                SGL::raiseError('your module directory must be writable '.
                    'by the webserver before you attempt this command',
                        SGL_ERROR_FILEUNWRITABLE);
                return;
            }
        }
        if (is_null($input->command)) {
            $input->command  = 'sgl-list-all';
        }
        $params = array();
        if ($input->mode) {
            $opts['mode'] = 'installed';
        }

        #PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));

        $cache = & SGL_Cache::singleton();
        $cacheId = 'pear'.$input->command.$input->mode;

        switch ($input->command) {

        case 'sgl-list-all':
            SGL::logMessage('made it to list-all', PEAR_LOG_DEBUG);
            if ($serialized = $cache->get($cacheId, 'pear')) {
                $data = unserialize($serialized);
                if (PEAR::isError($data)) {
                    return $data;
                }
                SGL::logMessage('pear data from cache', PEAR_LOG_DEBUG);
            } else {
                $cmd = PEAR_Command::factory($input->command, $config);
                $data = $cmd->run($input->command, $opts, $params);
                if (PEAR::isError($data)) {
                    return $data;
                }
                $serialized = serialize($data);
                $cache->save($serialized, $cacheId, 'pear');
                SGL::logMessage('pear data from db', PEAR_LOG_DEBUG);
            }
            break;

        case 'sgl-install':
        case 'sgl-uninstall':
        case 'sgl-upgrade':
            $params = array($input->pkg);
            $cmd = PEAR_Command::factory($input->command, $config);
            if (PEAR::isError($cmd)) {
                return SGL::raiseError('prob with PEAR_Command object');
            }
            ob_start();
            $ok = $cmd->run($input->command, $opts, $params);
            $pearOutput = ob_get_contents();
            ob_end_clean();

            if ($ok) {
                print $pearOutput;#exit;
                $this->_redirectToDefault($input, $output);
            } else {
                print '<pre>';print_r($ok);
            }
            break;
        }

       # foreach ($data['data'] as $aPackages) {
       #     foreach ($aPackages as $aPackage) {
                // [0] name
                // [1] remote version
                // [2] local version
                // [3] desc
                // [4] (array) deps
        #        $result .= $aPackage[0]."\n<br />";
#print '<pre>';print_r($aPackage);
         #   }
        #}
        $output->result = @$data['data'];
#print '<pre>';print_r($aPackage);

    }

    /**
     * Returns path to PEAR config file, and creates file if it doesn't exist.
     *
     * @return string
     */
    function getPearConfigPath()
    {
        if (!is_file(SGL_TMP_DIR . '/pear.conf')) {
            $conf = &PEAR_Config::singleton();

            $conf->set('default_channel', 'pear.php.net');
            $conf->set('http_proxy', SGL_LIB_PEAR_DIR);
            $conf->set('doc_dir', SGL_TMP_DIR);
            $conf->set('php_dir', SGL_LIB_PEAR_DIR);
            $conf->set('web_dir', SGL_WEB_ROOT);
            $conf->set('cache_dir', SGL_TMP_DIR);
            $conf->set('data_dir', SGL_TMP_DIR);
            $conf->set('test_dir', SGL_TMP_DIR);
            $conf->set('preferred_state', 'devel');

//            $conf->set('auto_discover ', '');
//            $conf->set('preferred_mirror', '');
//            $conf->set('remote_config', '');
//            $conf->set('bin_dir', '');
//            $conf->set('ext_dir', '');
//            $conf->set('php_bin', '');
//            $conf->set('cache_ttl', '');
//            $conf->set('umask', '');
//            $conf->set('verbose', '');
//            $conf->set('password', '');
//            $conf->set('sig_bin', '');
//            $conf->set('sig_keydir', '');
//            $conf->set('sig_keyid', '');
//            $conf->set('sig_type', '');
//            $conf->set('username', '');

            $ok = $conf->writeConfigFile(SGL_TMP_DIR . '/pear.conf', $layer = 'user'/*, $data = null*/);
        }

        return SGL_TMP_DIR . '/pear.conf';
    }
}