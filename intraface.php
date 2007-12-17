<?php
/**
 * Post install script for Intraface
 *
 * @package    SkipCheckIn
 * @author     Lars Olesen <lars@legestue.net>
 * @since      0.1.0
 * @version    @package-version@
 * @see        http://cvs.php.net/viewvc.cgi/pearweb/pearweb.php?revision=1.9&view=markup
 */

require_once 'Config.php';
require_once 'MDB2.php';
require_once 'MDB2/Schema.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Config.php';

class intraface_postinstall {

    /**
     * @var object PEAR_Config
     */
    private $_config;

    /**
     * @var object PEAR_Installer_Ui
     */
    private $_ui;

    /**
     * @var string realpath to the written config file
     */
    private $_config_file;

    /**
     * @var string path to web_dir
     */
    private $_web_dir;

    private $_last_installed_version;

    /**
     * @var string path to php_dir
     */
    private $_php_dir;

    /**
     * @var string path to data_dir
     */
    private $_data_dir;

    private $settings = array(
        0 => 'db_driver',
        1 => 'db_user',
        2 => 'db_pass',
        3 => 'db_host',
        4 => 'db_name',
        5 => 'net_scheme',
        6 => 'net_host',
        7 => 'net_directory', 'path_root', 'path_upload',
        8 => 'connection_internet',
        9 => 'server_status',
        10 => 'error_report_email',
        11 => 'error_log',
        12 => 'error_log_unique',
        13 => 'error_display_user',
        14 => 'error_display',
        15 => 'error_handle_level',
        16 => 'error_level_continue_script',
        17 => 'path_root',
        18 => 'path_upload',
        19 => 'timezone',
        20 => 'local',
        21 => 'intraface_intranetmaintenance_intranet_private_key',
        22 => 'intraface_xmlrpc_server_url',
        23 => 'intraface_xmlrpc_debug',
        24 => 'intraface_onlinepayment_provider',
        25 => 'intraface_onlinepayment_merchant',
        26 => 'intraface_onlinepayment_md5secret'
    );

    /**
     * Initialize this module
     *
     * Initialize PEAR environment objects. This method is called first from the Installer,
     * before the real post install process takes place. We do some initialization here.
     *
     * @param object(PEAR_Config) $config The PEAR configuration object, you can get and set
     *                                    all PEAR Installer configuration values using this.
     * @link http://pear.php.net/package/PEAR/docs/1.4.4/PEAR/PEAR_Config.html
     *
     * @param object(PEAR_PackageFile_v2) $self The parsed package.xml object, you can use
     *                                          this one to access the values defined in your
     *                                          package.xml.
     * @link http://pear.php.net/package/PEAR/docs/1.4.4/PEAR/PEAR_PackageFile_v2.html
     *
     * @param string $lastInstalledVersion The version number of your package, which is currently
     *                                     installed on the target system, or null, if this is the
     *                                     first time, your application is installed. We don't care
     *                                     about that in this example
     * @return bool True if initialized successfully, otherwise false.
     */
    public function init($config, $self = null, $lastInstalledVersion = null) {
        $this->_config = $config;
        $this->setUI();

        $this->_web_dir = '@web-dir@' . DIRECTORY_SEPARATOR;
        $this->_data_dir = '@data-dir@' . DIRECTORY_SEPARATOR . 'Intraface' . DIRECTORY_SEPARATOR;
        $this->_php_dir = '';
        $this->_config_file = $this->_web_dir . 'config.local.php';

        return true;
    }

    function setUI($ui = null)
    {
        if (!$ui)
            $this->_ui = PEAR_Frontend::singleton();
        else
            $this->_ui = $ui;

    }

    /**
     * Run the post installation process
     *
     * This method is called by the PEAR Installer each time, it has requested a set of data from the user,
     * meaning, after each <paramgroup> defined in the package.xml. On error, the PEAR Installer
     * calls this metod with "_undoOnError" as the $paramGroup value and has all information submitted so far
     * available in the $infoArray variable. In every other case the $paramGroup contains the name of the
     * performed <paramgroup> section and $infoArray contains the values selected from there.
     *
     * @param array  $answers The values entered by the user.
     * @param string $section The name of the parameter group we currently work on.
     *
     * @return bool  True if process went well, otherwise false to indicate that the process has to be repeated.
     */
    public function run($answers, $section) {
        // Choose, which <paramgroup> is processed now
        switch ($section) {
            case '_undoOnError':
                // We just give a message, usually you should try to revert the changes
                // you already made in this place.
                $this->_ui->outputData('An error occured during installation.');
                return false;
            case 'setup':
                $success = $this->setupEnvironment($answers);
                return true;
                break;
            default:
                echo 'ERROR: Unknown parameter group <'.$section.'>.';
                return false;
        }
    }

    /**
     * Process prompts before they are shown
     *
     * This method is called by the PEAR Installer before each prompt is shown. Use the method
     * to alter the text in the prompts.
     *
     * @param array  $prompts
     * @param string $section
     *
     * @return boolean
     */
    function postProcessPrompts($prompts, $section)
    {
        switch ($section) {
            case 'setup':
                if (file_exists($this->_config_file)) {
                    $this->_ui->outputData($this->_config_file);
                    require_once $this->_config_file;

                    foreach ($this->settings AS $key => $setting) {
                        $prompts[$key]['default'] = constant(strtoupper($setting));
                    }
                }
                break;
        }
        return $prompts;
    }

    /**
     * Creating the config file
     *
     * @param array $answers The $answers passed over from the run() method.
     *
     * @return bool True on success, otherwise false.
     */
    function setupEnvironment($answers)
    {
        $this->_ui->outputData('Writing config file');

        $config = new Config();
        $root = $config->getRoot();
        $root->createItem('directive', strtoupper('path_include_path'), '@php-dir@'.PATH_SEPARATOR);

        foreach ($this->settings AS $setting) {
            $root->createItem('directive', strtoupper($setting), $answers[$setting]);
        }

        $error_check = $config->writeConfig($this->_config_file, 'phpconstants', array('name' => 'config'));
        if (PEAR::isError($error_check)) {
            $this->_ui->outputData($error_check->getMessage());
            return false;
        }

        $this->_ui->outputData('Config file written: ' . $this->_config_file);

        return true;

    }
}
?>