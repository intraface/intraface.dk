<?php
/**
 * Shared components
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @example SharedExample.php
 */

class Shared {

    var $active;
    var $preload_file = array();
    var $shared_name;
    var $setting;
    var $controlpanel_files;
    var $frontpage_files; // til brug på forsiden
    var $translation;


    function __construct() {
        // init
        $this->shared_name = '';
        $this->active = 0;
    }

    /**
     * Denne funktion kï¿½res af kernel, nï¿½r man loader shared
     *
     */

    function load() {
        // Inkluder preload filerne

        for($i = 0, $max = count($this->preload_file); $i<$max; $i++) {
            $this->includeFile($this->preload_file[$i]);
        }
    }

    /**
     * Denne funktion bruges af SharedNavn.php til at fortï¿½lle, hvor includefilen til
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

    function addPreloadFile($file) {
        $this->preload_file[] = $file;
    }

    /**
   * Bruges til at inkludere fil
   *
   * ï¿½ndret med at tjekke om filen eksisterer.
   */
    function includeFile($file) {
        $file = PATH_INCLUDE_SHARED.$this->shared_name."/".$file;
        if (!file_exists($file)) {
            return 0;
        }
        require_once($file);
        return 1;
    }

    /*
    // virker det her endnu? // lars
    function addDependentModule($module) {
        $this->dependent_module[] = $module;
    }

    function getDependentModules() {
        return $this->dependent_module;
    }
    */

    function includeSettingFile($file) {
        global $_setting; // den globaliseres ogsï¿½ andre steder?
        require(PATH_INCLUDE_SHARED.$this->shared_name."/".$file);
    }

    function getPath() {
        return(PATH_WWW_SHARED.$this->shared_name."/");
    }

    /**
     * Bruges til at tilføje en setting til et modul, som skal være hardcoded ind i Main[Modulnavn]
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
