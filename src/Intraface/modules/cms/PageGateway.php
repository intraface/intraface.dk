<?php
class Intraface_modules_cms_PageGateway
{
    protected $db;
    protected $kernel;
    protected $dbquery;
    protected $cmssite;
    protected $values;

    /**
     *
     * @var array page status types
     */
    public $status_types = array(
        0 => 'draft',
        1 => 'published'
    );

    function __construct($kernel, DB_Sql $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    /**
     * Returns the possible page types
     *
     * @return array possible page types
     */
    public function getTypes()
    {
        return array(
            1 => 'page',
            2 => 'article',
            3 => 'news');
    }

    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        return ($this->dbquery = new Intraface_DBQuery($this->kernel, 'cms_page', 'cms_page.intranet_id = '.$this->kernel->intranet->get('id').' AND cms_page.active = 1 AND site_id = ' . $this->cmssite->get('id')));
    }

    function setDBQuery($dbquery)
    {
        $this->dbquery = $dbquery;
    }

    function findById($id)
    {
        $this->db->query("SELECT id, site_id FROM cms_page WHERE id = " . (int)$id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            return false;
        }
        $site = new CMS_Site($this->kernel, $this->db->f('site_id'));
        $object = new CMS_Page($site, (int)$id);
        return $object;
    }

    function findBySiteIdAndIdentifier($site_id, $identifier)
    {
        $identifier = strip_tags($identifier);

        if (!empty($identifier)) {
            $this->db->query("SELECT site_id, id FROM cms_page WHERE identifier = '" . $identifier . "' AND intranet_id = " . $this->kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $site_id);
        } else {
            // @todo choose the default page - vi skal lige have noget med publish og expire date her ogs�
            $this->db->query("SELECT site_id, id FROM cms_page WHERE intranet_id = " . $this->kernel->intranet->get('id') . " AND active = 1 AND status_key = 1 AND site_id = " . $site_id . " ORDER BY position ASC LIMIT 1");
        }
        if (!$this->db->nextRecord()) {
            $this->db->query("SELECT site_id, id FROM cms_page WHERE id = " . (int)$identifier . " AND intranet_id = " . $this->kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $site_id);
            if (!$this->db->nextRecord()) {
                return false;
            }
        }
        return new CMS_Page(new CMS_Site($this->kernel, $this->db->f('site_id')), $this->db->f('id'));
    }

    /**
     *
     * @param $site
     * @param object $page CMS_Page used when finding submenu in XMLRPC/shop/Server0030.php method getPage
     * @todo remove $page parameter and find another way to generate submenu
     * @return unknown_type
     */
    function findAllBySite($site, $page = null)
    {
        $this->cmssite = $site;
        $pages = array();

        if ($this->getDBQuery()->checkFilter('type') && $this->getDBQuery()->getFilter('page') == 'all') {
            // no condition isset
            // $sql_type = "";
        } else {
            // with int it will never be a fake searcy
            $type = $this->getDBQuery()->getFilter('type');
            if ($type == '') {
                $type = 'page'; // Standard
            }

            if ($type != 'all') {
                $type_key = array_search($type, $this->getTypes());
                if ($type_key === false) {
                    trigger_error("Invalid type '".$type."' set with CMS_PAGE::dbquery::setFilter('type') in CMS_Page::getList", E_USER_ERROR);
                }

                $this->getDBQuery()->setCondition("type_key = ".$type_key);
            }
        }


        // hvis en henter siderne uden for systemet
        $sql_expire = '';
        $sql_publish = '';
        // @todo This need to be corrected
        if (!is_object($this->kernel->user)) {
            $this->getDBQuery()->setCondition("(date_expire > NOW() OR date_expire = '0000-00-00 00:00:00') AND (date_publish < NOW() AND status_key > 0 AND hidden = 0)");
        }

        switch ($this->getDBQuery()->getFilter('type')) {
            case 'page':
                $this->getDBQuery()->setSorting("position ASC");
            break;
            case 'news':
                $this->getDBQuery()->setSorting("date_publish DESC");
            break;
            case 'article':
                $this->getDBQuery()->setSorting("position, date_publish DESC");
            break;
            default:
                $this->getDBQuery()->setSorting("date_publish DESC");
            break;
        }

        // rekursiv funktion til at vise siderne
        $pages = array();
        $go = true;
        $n = 0; // level
        // $o = 0; //
        $i = 0; // page counter
        // $level = 1;
        $cmspage = array();
        $cmspage[0] = new DB_Sql;

        // Benyttes til undersider.
        $dbquery_original = clone $this->getDBQuery();
        $dbquery_original->storeResult('','', 'toplevel'); // sikre at der ikke bliver gemt ved undermenuer.


        $keywords = $this->getDBQuery()->getKeyword();
        if (isset($keywords) && is_array($keywords) && count($keywords) > 0 && $type == 'page') {
            // If we are looking for pages, and there is keywords, we probaly want from more than one level
            // So we add nothing about level to condition.

        } elseif ($this->getDBQuery()->checkFilter('level') && $type == 'page') { // $level == 'sublevel' &&

            // Til at finde hele menuen p� valgt level.
            $page_tree = $page->get('page_tree');
            $level = (int)$this->getDBQuery()->getFilter('level');
            if (isset($page_tree[$level - 1]) && is_array($page_tree[$level - 1])) {
                $child_of_id = $page_tree[$level - 1]['id'];
            } else {
                $child_of_id = 0;
            }

            $this->getDBQuery()->setCondition('child_of_id = '.$child_of_id);
            // $cmspage[0]->query("SELECT *, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk FROM cms_page WHERE active=1 AND child_of_id = ".$this->id. $sql_expire . $sql_publish . " ORDER BY id");

        } else {
            $this->getDBQuery()->setCondition('child_of_id = 0');
            // $cmspage[0]->query("SELECT *, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk FROM cms_page WHERE ".$sql_type." site_id = " . $this->cmssite->get('id') . " AND child_of_id = 0 AND active = 1 " . $sql_expire . $sql_publish . $sql_order);
        }

        $cmspage[0] = $this->getDBQuery()->getRecordset("cms_page.id, title, identifier, status_key, navigation_name, date_publish, child_of_id, pic_id, description, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk", '', false); //

        while(TRUE) {
            while($cmspage[$n]->nextRecord()) {

                $pages[$i]['id'] = $cmspage[$n]->f('id');

                $pages[$i]['title'] = $cmspage[$n]->f('title');
                $pages[$i]['identifier'] = $cmspage[$n]->f('identifier');
                $pages[$i]['navigation_name'] = $cmspage[$n]->f('navigation_name');
                $pages[$i]['date_publish_dk'] = $cmspage[$n]->f('date_publish_dk');
                $pages[$i]['date_publish'] = $cmspage[$n]->f('date_publish');
                $pages[$i]['child_of_id'] = $cmspage[$n]->f('child_of_id');
                $pages[$i]['level'] = $n;

                if (empty($pages[$i]['identifier'])) {
                    $pages[$i]['identifier'] = $pages[$i]['id'];
                }
                if (empty($pages[$i]['navigation_name'])) {
                    $pages[$i]['navigation_name'] = $pages[$i]['title'];
                }

                $pages[$i]['status'] = $this->status_types[$cmspage[$n]->f('status_key')];

                // @todo hvad er det her til
                $pages[$i]['new_status'] = 'published';
                if ($pages[$i]['status'] == 'published') {
                    $pages[$i]['new_status'] = 'draft';
                }
                // hertil slut

                // denne b�r laves om til picture - og s� f�r man alle nyttige oplysninger ud
                $pages[$i]['pic_id'] = $cmspage[$n]->f('pic_id');
                $pages[$i]['picture'] = $this->getPicture($cmspage[$n]->f('pic_id'));

                //$pages[$i]['picture'] = $cmspage[$n]->f('pic_id');
                $pages[$i]['description'] = $cmspage[$n]->f('description');

                // til google sitemaps
                // sp�rgsm�let er om vi ikke skal starte et objekt op for hver pages

                $pages[$i]['url'] = $this->cmssite->get('url') . $pages[$i]['identifier'] . '/';
                $pages[$i]['url_self'] = $pages[$i]['identifier'] . '/';
                $pages[$i]['changefreq'] = 'weekly';
                $pages[$i]['priority'] = 0.5;

                $i++;
                // $o = $n + 1;

                if ($this->getDBQuery()->getFilter('type') == 'page' AND $this->getDBQuery()->getFilter('level') == 'alllevels') {
                    $dbquery[$n + 1] = clone $dbquery_original;
                    $dbquery[$n + 1]->setCondition("child_of_id = ".$cmspage[$n]->f("id"));
                    $cmspage[$n + 1] = $dbquery[$n + 1]->getRecordset("id, title, identifier, navigation_name, date_publish, child_of_id, pic_id, status_key, description, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk", '', false);

                    // if (!array_key_exists($n + 1, $cmspage) OR !is_object($cmspage[$n + 1])) {
                    //	$cmspage[$n + 1] = new DB_Sql;
                    //}
                    // $cmspage[$n + 1]->query("SELECT *, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk FROM cms_page WHERE active=1 AND child_of_id = ".$cmspage[$n]->f("id"). $sql_expire . $sql_publish . " ORDER BY id");

                    if ($cmspage[$n + 1]->numRows() != 0) {
                        $n++;
                        continue;
                    }
                }

            }

            if ($n == 0) {
                break;
            }

            $n--;
        }

        return $pages;

    }

    function getPicture($pic_id)
    {
        $shared_filehandler = $this->kernel->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

                $tmp_filehandler = new FileHandler($this->kernel, $pic_id);
                $this->value['picture']['id']                   = $pic_id;
                $this->value['picture']['original']['icon_uri'] = $tmp_filehandler->get('icon_uri');
                $this->value['picture']['original']['name']     = $tmp_filehandler->get('file_name');
                $this->value['picture']['original']['width']    = $tmp_filehandler->get('width');
                $this->value['picture']['original']['height']   = $tmp_filehandler->get('height');
                $this->value['picture']['original']['file_uri'] = $tmp_filehandler->get('file_uri');

                if ($tmp_filehandler->get('is_image')) {
                    $tmp_filehandler->createInstance();
                    $instances = $tmp_filehandler->instance->getList('include_hidden');
                    foreach ($instances as $instance) {
                        $this->value['picture'][$instance['name']]['file_uri'] = $instance['file_uri'];
                        $this->value['picture'][$instance['name']]['name']     = $instance['name'];
                        $this->value['picture'][$instance['name']]['width']    = $instance['width'];
                        $this->value['picture'][$instance['name']]['height']   = $instance['height'];

                    }
                }

            return $this->value['picture'];
    }

    /**
     * Returns the possible page types but with a binary index
     *
     * @return array possible page types with binary index
     */
    static public function getTypesWithBinaryIndex()
    {
        return array(
            1 => 'page',
            2 => 'article',
            4 => 'news');
    }

    function get($key)
    {
        return $this->values[$key];
    }
}