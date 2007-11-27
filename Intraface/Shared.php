<?php
/**
 * Shared components
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @example SharedExample.php
 */
class Shared
{
    protected $active;
    protected $preload_file = array();
    protected $shared_name;
    protected $setting;
    protected $controlpanel_files;
    protected $frontpage_files; // til brug på forsiden
    protected $translation;

    public function __construct() {
        // init
        $this->shared_name = '';
        $this->active = 0;
    }

    /**
     * Is run by kernel when loading a shared module
     */
    public function load()
    {
        // Inkluder preload filerne
        for($i = 0, $max = count($this->preload_file); $i<$max; $i++) {
            $this->includeFile($this->preload_file[$i]);
        }
    }

    /**
     * Denne funktion bruges af SharedNavn.php til at fortï¿½lle, hvor includefilen til
   * det enkelte modul ligger.
     */
    function addFrontpageFile($filename)
    {
        $this->frontpage_files[] = $filename;
    }

    function getFrontpageFiles()
    {
        return $this->frontpage_files;
    }

    function addControlpanelFile($title, $url)
    {
        $this->controlpanel_files[] = array(
            'title' => $title,
            'url' => $url
        );
    }

    function getControlpanelFiles()
    {
        return $this->controlpanel_files;
    }

    function addPreloadFile($file)
    {
        $this->preload_file[] = $file;
    }

    /**
     * Bruges til at inkludere fil
     *
     * @todo why is this not using getPath?
     */
    function includeFile($file)
    {
        $file = PATH_INCLUDE_SHARED . $this->shared_name . '/' . $file;
        if (!file_exists($file)) {
            return 0;
        }
        require_once($file);
        return 1;
    }

    /**
     * @todo why is this not using getPath()
     */
    function includeSettingFile($file)
    {
        global $_setting; // this is also globalized other places
        require(PATH_INCLUDE_SHARED . $this->shared_name . '/' . $file);
    }

    /**
     * @todo could this not resolve the path automatically, for instance through a dirname()
     *       would do it possible to drop a constant.
     */
    function getPath()
    {
        return(PATH_WWW_SHARED.$this->shared_name."/");
    }

    /**
     * Bruges til at tilføje en setting til et modul, som skal være hardcoded ind i Main[Modulnavn]
     */
    function addSetting($key, $value)
    {
        $this->setting[$key] = $value;
    }

    function getSetting($key)
    {
        if(isset($this->setting[$key])) {
            return($this->setting[$key]);
        }
    }
}