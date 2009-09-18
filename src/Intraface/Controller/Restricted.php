<?php
class Intraface_Controller_Restricted extends k_Component
{
    protected $registry;
    protected $kernel;

    protected function map($name)
    {
        if ($name == 'switchintranet') {
            return 'Intraface_Controller_SwitchIntranet';
        } elseif ($name == 'module') {
            return 'Intraface_Controller_ModuleGatekeeper';
        }
    }

    function dispatch()
    {
        if ($this->identity()->anonymous()) {
            throw new k_NotAuthorized();
        }
        return parent::dispatch();
    }

    function renderHtml()
    {
        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
			$this->getKernel()->setting->set('user', 'homepage.message', 'hide');
		}
        $smarty = new k_Template(dirname(__FILE__) . '/templates/restricted.tpl.php');
        return $smarty->render($this);
    }

    function getTranslation()
    {
        $language = $this->getKernel()->setting->get('user', 'language');

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

        $this->getKernel()->translation = $translation;
        return $translation;
    }

    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
    	return $this->kernel = $this->session()->get('kernel');
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getLastView()
    {
		$last_view = $this->getKernel()->setting->get('user', 'homepage.last_view');
		$this->getKernel()->setting->set('user', 'homepage.last_view', date('Y-m-d H:i:s'));
    	return $last_view;
    }

    function wrapHtml($content)
    {
        return sprintf('<html><body><ul><li><a href="'.$this->url('/restricted/module').'">Moduler</a></li><li><a href="'.$this->url('/logout').'">Logout</a></li><li><a href="'.$this->url('/restricted/switchintranet').'">Switch Intranet</a></li></ul>%s</body></html>', $content);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function t($phrase)
    {
        return $phrase;
    }
}