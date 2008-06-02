<?php
/**
 * Page.php
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 *
 */
class Intraface_Page 
{
    public $kernel;
    public $db;
    public $theme;
    public $usermenu;
    public $submenu;
    public $menu;
    public $javascript_path = array();
    public $primary_module;

    function __construct($object_kernel) 
    {
        if(!is_object($object_kernel)) {
            trigger_error('Page requires Kernel', E_USER_ERROR);
        }
        $this->kernel = $object_kernel;
        $this->db = new DB_Sql;
    }

    function start($title = '') 
    {
        $this->primary_module = $this->kernel->getPrimaryModule();
        $name = '';
        if (is_object($this->primary_module)) {
            $name = $this->primary_module->getName();
        }

        // brugermenuen
        // Unforntunately the usermenuen has to be before the cache as it is printed in bottom.php.
        $this->usermenu = array();
        $this->usermenu[0]['name'] = $this->kernel->translation->get('logout', 'common');
        $this->usermenu[0]['url'] = PATH_WWW.'main/logout.php';
        if(count($this->kernel->user->getIntranetList()) > 1) {
            $this->usermenu[1]['name'] = $this->kernel->translation->get('change intranet', 'common');
            $this->usermenu[1]['url'] = PATH_WWW.'main/change_intranet.php';
        }
        $this->usermenu[2]['name'] = $this->kernel->translation->get('control panel', 'common');;
        $this->usermenu[2]['url'] = PATH_WWW.'main/controlpanel/';

        if (!is_dir(PATH_CACHE)) {
            if(!mkdir(PATH_CACHE)) {
                trigger_error('Unable to create dir "'.PATH_CACHE.'" from constant PATH_CACHE', E_USER_ERROR);
                exit;
            }
            chmod(PATH_CACHE, 644);
        }

        $options = array(
            'cacheDir' => PATH_CACHE,
            'lifeTime' => 3600
        );
        $cache = new Cache_Lite_Output($options);

        // unfortunately cache has to be deactivated (true or) as there is problems with titel and javascript. solution: only caching of menu and nothing else.
        if (true OR !USE_CACHE OR !($cache->start('page_' . $this->kernel->user->get('id') . '_' . $name))) {

            if (!is_object($this->kernel->translation)) $this->kernel->getTranslation();


            $intranet_name = $this->kernel->intranet->get('name');

            if(empty($title)) {
                $title = $intranet_name;
            }

            // temaet
            $themes = themes();
            $this->theme_key = $this->kernel->setting->get('user', 'theme');

            // fontsize
            $this->fontsize = $this->kernel->setting->get('user', 'ptextsize');


            // menuen
            $this->menu = array();
            $i = 0;
            $this->menu[$i]['name'] = $this->kernel->translation->get('dashboard', 'dashboard');;
            $this->menu[$i]['url'] = PATH_WWW.'main/';
            $i++;
            $this->db->query("SELECT name, menu_label, name FROM module WHERE active = 1 AND show_menu = 1 ORDER BY menu_index");
            while($this->db->nextRecord()) {

                if($this->kernel->user->hasModuleAccess($this->db->f('name'))) {
                    $this->menu[$i]['name'] = $this->kernel->translation->get($this->db->f('name'), $this->db->f('name'));
                    $this->menu[$i]['url'] = PATH_WWW_MODULE.$this->db->f("name").'/';
                    $i++;
                }
            }

            $submenu = array();
            if(is_object($this->primary_module)) {
                $all_submenu = $this->primary_module->getSubmenu();
                if(count($all_submenu) > 0) { // added to avoid error messages
                    $j = 0;
                    for($i = 0, $max = count($all_submenu); $i < $max; $i++) {
                        $access = false;

                        if($all_submenu[$i]['sub_access'] != '') {
                            $sub = explode(":", $all_submenu[$i]['sub_access']);

                            switch($sub[0]) {
                                case 'sub_access':
                                    if($this->kernel->user->hasSubAccess($this->primary_module->module_name, $sub[1])) {
                                        $access = true;
                                    }
                                    break;

                                case 'module':
                                    if($this->kernel->user->hasModuleAccess($sub[1])) {
                                        $access = true;
                                    }
                                    break;

                                default:
                                    trigger_error('Der er ikke angivet om submenu skal tjekke efter sub_access eller module adgang, for undermenupunktet i Page->start();', E_USER_ERROR);
                                    break;
                            }
                        }
                        else {
                            $access = true;
                        }

                        if($access) {
                            $this->submenu[$j]['name'] = $this->kernel->translation->get($all_submenu[$i]['label'], $this->primary_module->getName());
                            $this->submenu[$j]['url'] = $this->primary_module->getPath().$all_submenu[$i]['url'];
                            $j++;
                        }
                    }
                }
            }

            $javascript = '';
            if(!empty($this->javascript_path) AND count($this->javascript_path) > 0) {
                for($i = 0, $max = count($this->javascript_path); $i < $max; $i++) {
                    $javascript .= '<script type="text/javascript" src="'.$this->javascript_path[$i].'"></script>' . "\n";
                }
            }
            /*
            $systemdisturbance = new SystemDisturbance($this->kernel);
            $now = $systemdisturbance->getActual();

            if(!empty($now) AND count($now) > 0 && $now['important'] == 1) {
                $system_message = $now['description'];
            }
            */


            //header('Vary: Accept');
            // setting headers to prevent browser to cache page
            // jf. http://dk.php.net/header/

            //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            //header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified

            // HTTP/1.1
            //header('Cache-Control: no-store, no-cache, must-revalidate');
            //header('Cache-Control: post-check=0, pre-check=0', false);

            // HTTP/1.0
            //header('Pragma: no-cache');

            /*
            if (defined('OB_START') AND OB_START == 'use') {
                ob_start(); // OB_HANDLER
            }
            */

            //contentNegotiation(CONTENTNEGOTIATION);

            include(PATH_INCLUDE_IHTML.'/intraface/top.php');

            $cache->end();
        }

    }

    function end() 
    {
        // printer sidens slut

        include(PATH_INCLUDE_IHTML.'/intraface/bottom.php');

        if (defined('OB_START') AND OB_START == 'use') {
            ob_end_flush();
        }
    }

    function includeJavascript($scope, $filename) 
    {

        if(!in_array($scope, array('global', 'module'), true)) {
            trigger_error("Første parameter er ikke enten 'global' eller 'module' i Page->includeJavascript", E_USER_ERROR);
        }

        if($scope == 'global') {
            $this->javascript_path[] = PATH_WWW.'javascript/'.$filename;
        }
        else {
            $this->javascript_path[] = 'javascript/'.$filename;
        }
    }
}
