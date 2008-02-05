<?php
/**
 *
 *
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 *
 * @example MainExample.php
 */

class Main {

    var $menu_label;
    var $show_menu;
    var $active;
    var $menu_index;
    var $frontpage_index;
    var $submenu = array();
    var $sub_access = array();
    var $sub_access_description = array();
    var $preload_file = array();
    var $dependent_module = array();
    var $required_shared = array();
    var $module_name;
    var $setting;
    var $controlpanel_files;
    var $frontpage_files; // til brug p� forsiden
    var $translation;
    var $kernel;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        // init
        $this->module_name = '';
        $this->menu_label = '';
        $this->show_menu = 0;
        $this->active = 0;
        $this->menu_index = 0;
        $this->frontpage_index = 0;
    }

    /**
     * Loads stuff about the module. Kernel runs it
     *
     * @return void
     */
    function load($kernel)
    {
        // Inkluder preload filerne
        $this->kernel = $kernel;

        if(is_array($this->required_shared) && count($this->required_shared) > 0) {
            foreach($this->required_shared AS $shared_name) {
                $this->kernel->useShared($shared_name);
            }
        }

        for($i = 0, $max = count($this->preload_file); $i<$max; $i++) {
            $this->includeFile($this->preload_file[$i]);
        }

    }

    /**
     * Denne funktion bruges af MainModulnavn.php til at fort�lle, hvor includefilen til
     * det enkelte modul ligger.
     *
     * @param string $filename
     *
     * @return void
     */
    function addFrontpageFile($filename)
    {
        $this->frontpage_files[] = $filename;
    }

    /**
     * Gets files to use on the frontpage
     *
     * @return array
     */
    function getFrontpageFiles()
    {
        return $this->frontpage_files;
    }

    /**
     * Gets files to use on the frontpage
     *
     * @param string $title Title
     * @param string $url   Url
     *
     * @return array
     */
    function addControlpanelFile($title, $url)
    {
        $this->controlpanel_files[] = array(
            'title' => $title,
            'url' => $url
        );
    }

    /**
     * Gets files to use on the frontpage
     *
     * @return array
     */
    function getControlpanelFiles()
    {
        return $this->controlpanel_files;
    }

    /**
     * Adds a submenu item
     *
     * @param string $label
     * @param string $url
     * @param string $sub_access @todo is this correct?
     *
     * @return void
     */
    function addSubmenuItem($label, $url, $sub_access = '')
    {
        $i = count($this->submenu);
        $this->submenu[$i]['label'] = $label;
        $this->submenu[$i]['url'] = $url;
        $this->submenu[$i]['sub_access'] = $sub_access;
    }

    /**
     * @return array
     */
    function getSubmenu()
    {
        return($this->submenu);
    }

    /**
     * @param string $name
     * @param string $description
     *
     * @return void
     */
    function addSubAccessItem($name, $description)
    {
        array_push($this->sub_access, $name);
        array_push($this->sub_access_description, $description);
    }

    /**
     * @param string $file
     *
     * @return void
     */
    function addPreloadFile($file)
    {
        $this->preload_file[] = $file;
    }

    /**
     * Bruges til at inkludere fil
     *
     * @param string $file @todo name or?
     *
     * @return boolean
     */
    function includeFile($file)
    {
        // @todo constant should be removed
        $file = PATH_INCLUDE_MODULE . $this->module_name . '/' . $file;
        if (!file_exists($file)) {
            return false;
        }
        require_once($file);
        return true;
    }

    /**
     * Inkluderer automatisk et andet modul. Man skal dog have adgang til det andet modul.
     *
     * @param string $module
     */
    function addDependentModule($module)
    {
        $this->dependent_module[] = $module;
    }

    /**
     * @return array
     */
    function getDependentModules()
    {
        return $this->dependent_module;
    }

    /**
     * Giver mulighed for at inkludere shared der skal benyttes overalt i modullet.
     *
     * @param string $shared @todo is this correct
     *
     * @return void
     */
    function addRequiredShared($shared)
    {
        $this->required_shared[] = $shared;
    }

    /**
     *
     * @return array
     */
    function getRequiredShared()
    {
        return $this->required_shared;
    }

    /**
     * @todo problem med at denne er globaliseret
     *
     * @param string $file @todo is this correct
     *
     * @return void
     */
    function includeSettingFile($file)
    {
        // @todo global should be removed
        global $_setting; // globalized other places also
        // @todo constant should be removed
        include(PATH_INCLUDE_MODULE.$this->module_name."/".$file);
    }

    /**
     * @return string
     */
    function getPath()
    {
        // @todo constant should be removed
        return(PATH_WWW_MODULE.$this->module_name."/");
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    function addSetting($key, $value)
    {
        $this->setting[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    function getSetting($key)
    {
        if(isset($this->setting[$key])) {
            return($this->setting[$key]);
        }
    }

    /**
     * @return string
     */
    function getName()
    {
        return($this->module_name);
    }

    /**
     * @return integer
     */
    function getId()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM module WHERE name = '".$this->module_name."'");
        if ($db->nextRecord()) {
            return $db->f('id');
        }
        return 0;
    }
}
