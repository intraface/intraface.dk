<?php
/**
 * Page
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

    function __construct(Intraface_Kernel $object_kernel, MDB2_Driver_Common $db = null)
    {
        $this->kernel = $object_kernel;
        if ($this->db == null) {
            $this->db = MDB2::singleton(DB_DSN);
        } else {
            $this->db = $db;
        }
    }

    function start($title = '')
    {
        $this->primary_module = $this->kernel->getPrimaryModule();
        $name = '';
        if (is_object($this->primary_module)) {
            $name = $this->primary_module->getName();
        }

        /*
        // brugermenuen
        // Unforntunately the usermenuen has to be before the cache as it is printed in bottom.php.
        $this->usermenu = array();
        $this->usermenu[0]['name'] = $this->t('logout', 'common');
        $this->usermenu[0]['url'] = url('/core/logout');
        if (method_exists('getIntranetList', $this->kernel->user)) {
        if (count($this->kernel->user->getIntranetList()) > 1) {
            $this->usermenu[1]['name'] = $this->t('change intranet', 'common');
            $this->usermenu[1]['url'] = url('/main/change_intranet.php');
        }
        }
        $this->usermenu[2]['name'] = $this->t('control panel', 'common');;
        $this->usermenu[2]['url'] = url('/core/restricted/module/controlpanel/');
		*/
/*
        if (!is_dir(PATH_CACHE)) {
            if (!mkdir(PATH_CACHE)) {
                throw new Exception('Unable to create dir "'.PATH_CACHE.'" from constant PATH_CACHE');
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
        // If you activate again remeber to activate $cache->end() in the end of this method
        if (true or defined('USE_CACHE') AND !USE_CACHE OR !($cache->start('page_' . $this->kernel->user->get('id') . '_' . $name))) {
            if (!is_object($this->kernel->translation)) $this->kernel->getTranslation();
*/
            $intranet_name = $this->kernel->intranet->get('name');

        if (empty($title)) {
            $title = $intranet_name;
        }

            // temaet
            $themes = self::themes();
            $this->theme_key = $this->kernel->setting->get('user', 'theme');

            // fontsize
            $this->fontsize = $this->kernel->setting->get('user', 'ptextsize');


            // menuen
            $this->menu = array();
            $i = 0;
            $this->menu[$i]['name'] = $this->t('dashboard', 'dashboard');
        ;
            $this->menu[$i]['url'] = url('/core/restricted/');
            $i++;
            $res = $this->db->query("SELECT name, menu_label, name FROM module WHERE active = 1 AND show_menu = 1 ORDER BY menu_index");
        foreach ($res->fetchAll() as $row) {
            if ($this->kernel->user->hasModuleAccess($row['name'])) {
                $this->menu[$i]['name'] = $this->t($row['name'], $row['name']);
                $this->menu[$i]['url'] = url('/core/restricted/module/' . $row["name"]);
                $i++;
            }
        }

            $submenu = array();
        if (is_object($this->primary_module)) {
            $all_submenu = $this->primary_module->getSubmenu();
            if (count($all_submenu) > 0) { // added to avoid error messages
                $j = 0;
                for ($i = 0, $max = count($all_submenu); $i < $max; $i++) {
                    $access = false;

                    if ($all_submenu[$i]['sub_access'] != '') {
                        $sub = explode(":", $all_submenu[$i]['sub_access']);

                        switch ($sub[0]) {
                            case 'sub_access':
                                if ($this->kernel->user->hasSubAccess($this->primary_module->module_name, $sub[1])) {
                                    $access = true;
                                }
                                break;

                            case 'module':
                                if ($this->kernel->user->hasModuleAccess($sub[1])) {
                                    $access = true;
                                }
                                break;

                            default:
                                throw new Exception('Der er ikke angivet om submenu skal tjekke efter sub_access eller module adgang, for undermenupunktet i Page->start();');
                                break;
                        }
                    } else {
                        $access = true;
                    }

                    if ($access) {
                        $this->submenu[$j]['name'] = $this->t($all_submenu[$i]['label'], $this->primary_module->getName());
                        $this->submenu[$j]['url'] = $this->primary_module->getPath(). $all_submenu[$i]['url'];
                        $j++;
                    }
                }
            }
        }

            $javascript = '';
        if (!empty($this->javascript_path) and count($this->javascript_path) > 0) {
            for ($i = 0, $max = count($this->javascript_path); $i < $max; $i++) {
                $javascript .= '<script type="text/javascript" src="'.$this->javascript_path[$i].'"></script>' . "\n";
            }
        }
            /*
            $systemdisturbance = new SystemDisturbance($this->kernel);
            $now = $systemdisturbance->getActual();

            if (!empty($now) AND count($now) > 0 && $now['important'] == 1) {
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

            //include(PATH_INCLUDE_IHTML.'/intraface/top.php');

            // $cache->end();
        //}

    }

    function end()
    {
        $this->usermenu = array();
        $this->usermenu[0]['name'] = $this->t('logout', 'common');
        $this->usermenu[0]['url'] = url('/core/logout');

        if (method_exists($this->kernel->user, 'getIntranetList')) {
            if (count($this->kernel->user->getIntranetList()) > 1) {
                $this->usermenu[1]['name'] = $this->t('change intranet', 'common');
                $this->usermenu[1]['url'] = url('/core/restricted/switchintranet');
            }
        }
        $this->usermenu[2]['name'] = $this->t('control panel', 'common');
        ;
        $this->usermenu[2]['url'] = url('/core/restricted/module/controlpanel');

        //include(PATH_INCLUDE_IHTML.'/intraface/bottom.php');
    }

    function includeJavascript($scope, $filename)
    {
        if (!in_array($scope, array('global', 'module'), true)) {
            throw new Exception("F�rste parameter er ikke enten 'global' eller 'module' i Page->includeJavascript");
        }

        if ($scope == 'global') {
            $this->javascript_path[] = url('/javascript/'.$filename);
        } else {
            $this->javascript_path[] = 'javascript/'.$filename;
        }
    }

    function addJavascript($url)
    {
        $this->javascript_path[] = $url;
    }

    public static function themes()
    {
        return array(
            1 => array(
              'label' => 'system',
              'dir' => '/system/',
                    'name' => 'Web 2.0',
                    'description' => 'Spr�lsk, vildt og pastel.'
            ),
            2 => array(
              'label' => 'newsystem',
              'dir' => '/newsystem/',
                    'name' => 'Standard',
                    'description' => 'Standard.'
            ),
            3 => array(
              'label' => 'black',
              'dir' => '/black/',
                    'name' => 'Den sorte ridder',
                    'description' => 'Det gamle tema. Nydeligt og kedeligt.'
            )
        );
    }

    function t($phrase, $page_id = null)
    {
        return $this->kernel->translation->get($phrase, $page_id);
    }
}
