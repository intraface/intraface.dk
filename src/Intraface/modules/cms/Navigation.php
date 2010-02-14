<?php
/**
 *
 * TODO Vi skal finde ud af hvordan vi kan sikre os at undermenuerne
 *       bliver der p� undersiderne til en undermenu.
 * @package Intraface_CMS
 */
class CMS_Navigation extends Intraface_Standard
{
    private $cmspage;
    public $value;

    function __construct($cmspage)
    {
        $this->cmspage = $cmspage;
    }

    function build($level = 'toplevel')
    {
        $i = 0;
        $this->cmspage->getDBQuery()->clearAll();
        $this->cmspage->getDBQuery()->setFilter('type', 'page');
        $this->cmspage->getDBQuery()->setFilter('level', $level);
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