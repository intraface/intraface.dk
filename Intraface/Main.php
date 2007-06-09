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


    function Main() {
        // init
        $this->module_name = '';
        $this->menu_label = '';
        $this->show_menu = 0;
        $this->active = 0;
        $this->menu_index = 0;
        $this->frontpage_index = 0;
    }



    /**
     * Denne funktion k�res af kernel, n�r man loader modulet
     *
     */

    function load(&$kernel) {
        // Inkluder preload filerne
        $this->kernel = &$kernel;

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
     */
    function addFrontpageFile($filename) {
        $this->frontpage_files[] = $filename;
    }

    function getFrontpageFiles() {
        return $this->frontpage_files;
    }

    function addControlpanelFile($title, $url) {
        $this->controlpanel_files[] = array(
            'title' => $title,
            'url' => $url
        );
    }

    function getControlpanelFiles() {
        return $this->controlpanel_files;
    }


    /**
     * Tilf�jer et undermenu punkt
     * Benyttes fra
     */
    function addSubmenuItem($label, $url, $sub_access = '') {
        $i = count($this->submenu);
        $this->submenu[$i]['label'] = $label;
        $this->submenu[$i]['url'] = $url;
        $this->submenu[$i]['sub_access'] = $sub_access;
    }

    function getSubmenu() {
        return($this->submenu);
    }

    function addSubAccessItem($name, $description) {
        array_push($this->sub_access, $name);
        array_push($this->sub_access_description, $description);
    }

    function addPreloadFile($file) {
        $this->preload_file[] = $file;
    }

    /**
   * Bruges til at inkludere fil
   *
   * �ndret med at tjekke om filen eksisterer.
   */
    function includeFile($file) {
        $file = PATH_INCLUDE_MODULE.$this->module_name."/".$file;
        if (!file_exists($file)) {
            return 0;
        }
        require_once($file);
        return 1;
    }

    /**
     *  Inkluderer automatisk et andet modul. Man skal dog have adgang til det andet modul.
     */
    function addDependentModule($module) {
        $this->dependent_module[] = $module;
    }

    function getDependentModules() {
        return $this->dependent_module;
    }

    /**
     * Giver mulighed for at inkludere shared der skal benyttes overalt i modullet.
     *
     */
    function addRequiredShared($shared) {
        $this->required_shared[] = $shared;
    }

    function getRequiredShared() {
        return $this->required_shared;

        // print_r($this->required_shared);
        // die();
    }


    function includeSettingFile($file) {
        global $_setting; // den globaliseres ogs� andre steder?
        include(PATH_INCLUDE_MODULE.$this->module_name."/".$file);
    }

    function getPath() {
        return(PATH_WWW_MODULE.$this->module_name."/");
    }

  /*
    // sikkert overfl�dig
    function getValidationPath() {
        return(INCLUDE_MODULE.$this->module_name."/validationrules/");
    }
    */

    /**
     * F�lgende to funktioner b�r vel droppes?
     */

    function getId() {
        $db = new DB_Sql;
        $db->query("SELECT id FROM module WHERE name = '".$this->module_name."'");
        if ($db->nextRecord()) return $db->f('id');
        return 0;
    }


    function getName() {
        return($this->module_name);
    }

    /**
   * Bruges til at tilf�je en setting til et modul, som skal v�re hardcoded ind i Main[Modulnavn]
   */
    function addSetting($key, $value) {
        $this->setting[$key] = $value;
    }

    function getSetting($key) {
        if(isset($this->setting[$key])) {
            return($this->setting[$key]);
        }
    }
  /*
  function addTranslation($shortterm, $translation) {
    $this->translation[$shortterm] = $translation;
  }

    function getTranslation($shortterm) {
        if (!empty($this->translation[$shortterm])) {
            return $this->translation[$shortterm];
        }
        return $shortterm;
    }
  */
}

?>
