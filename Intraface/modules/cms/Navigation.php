<?php
/**
 *
 * TODO Vi skal finde ud af hvordan vi kan sikre os at undermenuerne
 *       bliver der på undersiderne til en undermenu.
 * @package Intraface_CMS
 */

class CMS_Navigation extends Standard
{

    var $cmspage;
    var $value;

    function __construct($cmspage)
    {
        if (!is_object($cmspage) OR strtolower(get_class($cmspage)) != 'cms_page') {
            trigger_error('CMS_Navigation::__construct needs CMS_Page', E_USER_ERROR);
        }
        $this->cmspage = & $cmspage;
    }

    function build($level = 'toplevel')
    { // 'toplevel'

        $i = 0;
        $this->cmspage->dbquery->clearAll();
        $this->cmspage->dbquery->setFilter('type', 'page');
        $this->cmspage->dbquery->setFilter('level', $level);
        $pages = $this->cmspage->getList();
        if (!is_array($pages) OR count($pages) == 0) {
            return array();
        }
        foreach ($pages AS $page) {
            if ($this->cmspage->get('id') == $page['id']) {
                $output[$i]['current'] = 'yes';
            } else {
                $output[$i]['current'] = 'no';
            }

            $output[$i]['url'] = $page['url'];
            $output[$i]['url_self'] = $page['url_self'];
            $output[$i]['title'] = $page['title'];
            $output[$i]['identifier'] = $page['identifier'];
            $output[$i]['navigation_name'] = $page['navigation_name'];
            $i++;
        }
        return $output;
    }
}