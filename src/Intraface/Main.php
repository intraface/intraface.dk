<?php
/**
 *
 *
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 *
 * @example MainExample.php
 */
abstract class Intraface_Main
{
    public $menu_label = '';
    public $active = 0;
    public $menu_index = 0;
    public $sub_access = array();
    public $sub_access_description = array();
    public $module_name = '';
    public $all_has_access = 0;
    protected $show_menu = 0;
    protected $frontpage_index = 0;
    protected $submenu = array();
    protected $preload_file = array();
    protected $dependent_module = array();
    protected $required_shared = array();
    protected $setting = array();
    protected $controlpanel_files = array();
    protected $frontpage_files = array();
    protected $shared = false;
    private $translation; // @todo used for what
    private $kernel; // @todo used for what
    protected $required = 0;

    /**
     * Loads stuff about the module. Kernel runs it
     *
     * @return void
     */
    function load($kernel)
    {
        // Inkluder preload filerne
        // @todo kernel is as far as I can tell only used here.
        //       therefore it should be made local
        $this->kernel = $kernel;

        if (is_array($this->required_shared) && count($this->required_shared) > 0) {
            foreach ($this->required_shared as $shared_name) {
                $this->kernel->useShared($shared_name);
            }
        }

        foreach ($this->preload_file as $file) {
            $this->includeFile($file);
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

        include(dirname(__FILE__). '/modules/' .$this->module_name.'/'.$file);
    }

    /**
     * Returns the www path for the modules; always ends on a slash
     *
     * @return string
     */
    public function getPath()
    {
        return PATH_WWW . 'restricted/module/' . $this->module_name . '/';
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
        if (isset($this->setting[$key])) {
            return $this->setting[$key];
        }
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->module_name;
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

    function getShowMenu()
    {
        return $this->show_menu;
    }

    function getFrontpageIndex()
    {
        return $this->show_menu;
    }

    function getMenuLabel()
    {
        return $this->menu_label;
    }

    function getMenuIndex()
    {
        return $this->menu_index;
    }

    function isActive()
    {
        return $this->active;
    }

    function isShared()
    {
        return $this->shared;
    }

    function isRequired()
    {
        return $this->required;
    }
}
