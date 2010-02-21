<?php
/**
 * Page
 *
 * Sp�rgsm�l:
 * ---------
 *
 * 0.
 * For komplicerede sider kan det m�ske v�re en ide at omskrive get() til
 * at den loader specifikke ting, n�r der bliver spurgt efter dem:
 *
 * Fx kunne stylesheet og navigation v�re en ting, der f�rst skulle loades, hvis der sp�rges.
 *
 * 1.
 * Hvordan f�r vi opbygget URLs fornuftigt - s� vi kan have:
 *
 * http://www.site.dk/artikler/myteeth/
 * http://www.site.dk/produkter/dimser/
 * http://www.site.dk/kundensegetbibliotektilsiden/identifier/
 *
 * Det kan m�ske v�re et eller andet med at smide de enkelte sider
 * i nogle kategorier? Men hvad s� med de sider, der ikke er i kategorier?
 * Vi skal have et eller andet der g�r det let at s�tte op?
 *
 * 2.
 * Hvis vi skal have en side med artikler med alle n�gleordene, der er brugt
 * p� en anden side. Hvordan skal vi s� oprette denne faste side?
 *
 * 3.
 * Der skal knyttes billeder til de enkelte sider, s� man kan bruge billeder p� sidelister.
 *
 * 4.
 * Rettigheder skal v�lges til de enkelte sider. Creative commons.
 * Man skal p� siteniveau og templateniveau kunne s�tte ens foretrukne licens.
 *
 * <!--Creative Commons License-->
 * <a rel="license" href="http://creativecommons.org/licenses/by-nd/2.5/dk/">
 *		<img alt="Creative Commons License" style="border-width: 0" src="http://creativecommons.org/images/public/somerights20.png"/></a>
 *		<br/>Dette v�rk er licensieret under en <a rel="license" href="http://creativecommons.org/licenses/by-nd/2.5/dk/">Creative Commons Navngivelse-Ingen bearbejdelser 2.5 Danmark Licens</a>.
 *	<!--/Creative Commons License-->
 *	<!-- <rdf:RDF xmlns="http://web.resource.org/cc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
 *	<Work rdf:about="">
 *		<license rdf:resource="http://creativecommons.org/licenses/by-nd/2.5/dk/" />
 *	<dc:type rdf:resource="http://purl.org/dc/dcmitype/Text" />
 *	</Work>
 *	<License rdf:about="http://creativecommons.org/licenses/by-nd/2.5/dk/"><permits rdf:resource="http://web.resource.org/cc/Reproduction"/><permits rdf:resource="http://web.resource.org/cc/Distribution"/><requires rdf:resource="http://web.resource.org/cc/Notice"/><requires rdf:resource="http://web.resource.org/cc/Attribution"/></License></rdf:RDF> -->
 *
 * Se eksempel p� /test/update.php
 *
 * @package Intraface_CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   1.0
 * @version 1.0
 *
 */
class CMS_Page extends Intraface_Standard
{
    public $id;
    public $kernel;
    public $position;
    public $error;
    private $dbquery;

    public $cmssite;
    public $template;
    public $navigation;
    public $message;
    public $cc_license;

    public $value;
    public $status = array(
        0 => 'draft',
        1 => 'published'
    );

    public function __construct($cmssite, $id = 0)
    {
        if (!is_object($cmssite)) {
             trigger_error('CMS_Page::__construct needs CMS_Site', E_USER_ERROR);
        }

        $this->id         = (int)$id;
        $this->cmssite    =  $cmssite;
        $this->navigation = new CMS_Navigation($this);
        $this->template   = new CMS_Template($this->cmssite);
        $this->kernel     = $this->cmssite->kernel;
        $this->error      = new Intraface_Error();
        $this->value['active'] = 1;
        $this->value['status_key'] = 0;
        // $this->dbquery = $this->getDBQuery();

        // get settings
        $cms_module       = $this->kernel->module('cms');
        $this->cc_license = $cms_module->getSetting('cc_license');

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getDBQuery()
    {
        if ($this->dbquery) {
            return $this->dbquery;
        }
        return ($this->dbquery = new Intraface_DBQuery($this->kernel, 'cms_page', 'cms_page.intranet_id = '.$this->kernel->intranet->get('id').' AND cms_page.active = 1 AND site_id = ' . $this->cmssite->get('id')));
    }

    /**
     * Returns position object
     *
     * @param object $db database object
     * @return object Position
     */
    public function getPosition($db)
    {
        return new Ilib_Position($db, "cms_page", $this->id, "site_id=".$this->cmssite->get('id')." AND active = 1 AND type_key = 1", "position", "id");
    }

    /**
     * returns Template object
     *
     * @return object Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    function factory($kernel, $type, $value)
    {
        $gateway = new Intraface_modules_cms_PageGateway($kernel, new DB_Sql);

        switch ($type) {
            case 'id':
                return $gateway->findById($value);
                /*
                $db = new DB_Sql;
                $db->query("SELECT id, site_id FROM cms_page WHERE id = " . (int)$value . " AND intranet_id = " . $kernel->intranet->get('id'));

                if (!$db->nextRecord()) {
                    return false;
                }
                $site = new CMS_Site($kernel, $db->f('site_id'));
                $object = new CMS_Page($site, (int)$value);
                return $object;
                */
                break;
            case 'identifier':
                $value['identifier'] = safeToDb($value['identifier']);
                $value['identifier'] = strip_tags($value['identifier']);
                return $gateway->findByIdentifier($value['identifier']);

                /*
                $db = new DB_Sql;

                if (!empty($value['identifier'])) {
                    $db->query("SELECT site_id, id FROM cms_page WHERE identifier = '" . $value['identifier'] . "' AND intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $value['site_id']);
                } else {
                    // choose the default page - vi skal lige have noget med publish og expire date her ogs�
                    $db->query("SELECT site_id, id FROM cms_page WHERE intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND status_key = 1 AND site_id = " . $value['site_id'] . " ORDER BY position ASC LIMIT 1");
                }
                if (!$db->nextRecord()) {
                    $db->query("SELECT site_id, id FROM cms_page WHERE id = " . (int)$value['identifier'] . " AND intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $value['site_id']);
                    if (!$db->nextRecord()) {
                        return false;
                    }
                }
                return new CMS_Page(new CMS_Site($kernel, $db->f('site_id')), $db->f('id'));
                */
                break;
            default:
                trigger_error('CMS_Page::factory unknown type', E_USER_ERROR);
                break;
        }
    }


    /**
     * Valideringsfunktion
     */
    function validate($var)
    {
        $validator = new Intraface_Validator($this->error);
        
        $validator->isString($var['title'], 'Error in title', '', '');
        
        if (!empty($var['navigation_name'])) {
            $validator->isString($var['navigation_name'], 'error in navigation_name - has to be a string', '', 'allow_empty');
        }

        $validator->isString($var['keywords'], 'error in keywords', '', 'allow_empty');
        $validator->isString($var['description'], 'error in description', '', 'allow_empty');

        $validator->isNumeric($var['allow_comments'], 'error in comments - allowed values are 0 and 1');
        $validator->isNumeric($var['hidden'], 'error in hidden - allowed values are 0 and 1');

        if (!Validate::string($var['identifier'], array('format' => VALIDATE_ALPHA . VALIDATE_NUM . '-_'))) {
            $this->error->set('error in unique page address. allowed values are a-z 1-9 _ -');
        }
        if (!$this->isIdentifierAvailable($var['identifier'])) {
            $this->error->set('the choosen unique page address is already used for another page, article or news. please select another one');
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function isIdentifierAvailable($identifier)
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM cms_page WHERE site_id = " . $this->cmssite->get('id') . " AND identifier = '".$identifier."' AND active = 1 AND id != " . (int)$this->get('id'));
        return ($db->numRows() == 0);
    }

    /**
     * Vi skal have gemt positionen for siden ogs�
     *
     * Hvis date_publish ikke er sat, skal den bare tage dd.
     * Hvis date_expire ikke er sat, hvad skal den s� g�re?
     */
    function save($var)
    {
        $var = safeToDb($var);
        if (empty($var['allow_comments'])) {
            $var['allow_comments'] = 0;
        }
        if (empty($var['hidden'])) {
            $var['hidden'] = 0;
        }

        if (empty($var['keywords'])) {
            $var['keywords'] = '';
        }
        if (empty($var['description'])) {
            $var['description'] = '';
        }

        if (!isset($var['pic_id'])) {
            $var['pic_id'] = 0;
        }

        $type_key = array_search($var['page_type'], $this->getTypes());

        if (empty($var['date_publish'])) {
            $sql_publish = 'NOW()';
        } else {
            $sql_publish = "'".$var['date_publish']."'";
        }

        if (empty($var['identifier'])) {
            $var['identifier'] = md5(date('d-m-Y H:i:s') . $type_key . serialize($var));
        }

        settype($var['date_expire'], 'string');

        if (!$this->validate($var)) {
            return 0;
        }

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        } else {
            $sql_type = "UPDATE ";
            $sql_end = ", date_updated = NOW() WHERE id = " . $this->id;
        }

        $sql_extra = '';

        if (!empty($var['navigation_name'])) {
            $sql_extra .= "navigation_name = '".$var['navigation_name']."', ";
        }
        if (isset($var['child_of_id'])) {
            $sql_extra .= "child_of_id = '".(int)$var['child_of_id']."', ";
        }

        // if the page is to updated
        $sql = $sql_type . " cms_page SET
            intranet_id = '".$this->kernel->intranet->get('id')."',
            user_id = '".$this->kernel->user->get('id')."',
            title = '" .$var['title']. "',
            keywords = '" .$var['keywords']. "',
            description = '".$var['description']."',
            date_publish = ".$sql_publish.",
            allow_comments = ".$var['allow_comments'].",
            hidden = ".$var['hidden'].",
            date_expire = '".$var['date_expire']."',
            type_key = ".(int)$type_key.",
            ".$sql_extra."
            date_updated = NOW(),
            site_id = '".(int)$this->cmssite->get('id')."',
            template_id = ".$var['template_id'].",
            pic_id = ".intval($var['pic_id']).",
            identifier = '".$var['identifier']."'" . $sql_end;
        // password = '".$var['password']."',
        $db = new DB_Sql;
        $db->query($sql);

        $need_to_add_keywords = false;

        if ($this->id == 0) {
            $this->id = $db->insertedId();
            $need_to_add_keywords = true;
        }
        $this->load();


        //position
        $db->query("SELECT position FROM cms_page WHERE id = " . $this->id);
        if ($db->nextRecord()) {
            if ($db->f('position') == 0 AND count($this->getList($this->value['type']) > 0)) {
                $next_pos = $this->getPosition(MDB2::singleton(DB_DSN))->getMaxPosition() + 1;
                $db->query("UPDATE cms_page SET position = " . $next_pos . " WHERE id = " . $this->id);
            }
        }

        if ($need_to_add_keywords) {
            $this->template->getKeywords();
            $keywords_to_add = $this->template->getKeywordAppender()->getConnectedKeywordsAsString();
            $string_appender = new Intraface_Keyword_StringAppender($this->getKeywords(), $this->getKeywordAppender());
            $string_appender->addKeywordsByString($keywords_to_add);
        }

        return $this->id;
    }


    /**
     * loads the content for a page
     *
     * @todo hvad skal den helt n�jagtig loade? - skal den fx loadde elementerne ogs�?
     * Hvis vi er inde i redigering, kan den loade alt, men hvis vi er ved weblogin, s
     * skal den kun kunne loade nogle sider.
     *
     * @return boolean
     */
    function load()
    {
        if ($this->id <= 0) {
            return false;
        }

        $sql_expire = '';
        $sql_publish = '';
        if (!is_object($this->kernel->user)) {
            $sql_expire = " AND (date_expire > NOW() OR date_expire = '0000-00-00 00:00:00')";
            $sql_publish = " AND date_publish < NOW() AND status_key > 0";
        }

        $sql = "SELECT *, DATE_FORMAT(date_publish, '%d-%m-%Y') AS date_publish_dk FROM cms_page WHERE intranet_id = ".$this->cmssite->kernel->intranet->get('id')." AND id = " .$this->id . $sql_expire . $sql_publish;

        $db = new DB_Sql();
        $db->query($sql);

        if (!$db->nextRecord()) {
            return false;
        }

        $this->value['id'] = $db->f('id');
        $this->value['active'] = $db->f('active');
        $this->value['site_id'] = $db->f('site_id');
        $this->value['type_key'] = $db->f('type_key');
        $types = $this->getTypes();
        $this->value['type'] = $types[$db->f('type_key')];
        $this->value['identifier'] = $db->f('identifier');
        if (empty($this->value['identifier'])) {
            $this->value['identifier'] = $db->f('id');
        }
        $this->value['url'] =  $this->cmssite->get('url') . $this->value['identifier'] . '/';
        $this->value['url_self'] =  $this->value['identifier'] . '/';

        $this->value['child_of_id'] = $db->f('child_of_id');
        $this->value['name'] = $db->f('title'); //  bruges til keywords - m�ske skulle vi have et felt ogs�, s� title var webrelateret?
        $this->value['title'] = $db->f('title');
        $this->value['navigation_name'] = $db->f('navigation_name');
        if (empty($this->value['navigation_name'])) {
            $this->value['navigation_name'] = $this->value['title'];
        }
        $this->value['description'] = $db->f('description');
        $this->value['keywords'] = $db->f('keywords');
        $this->value['date_created'] = $db->f('date_created');
        $this->value['date_updated'] = $db->f('date_updated');
        $this->value['date_publish_dk'] = $db->f('date_publish_dk');
        $this->value['date_publish'] = $db->f('date_publish');
        $this->value['date_expire'] = $db->f('date_expire');
        $this->value['status_key'] = $db->f('status_key');
        $this->value['status'] = $this->status[$db->f('status_key')];
        $this->value['pic_id'] = $db->f('pic_id');
        $this->value['allow_comments'] = $db->f('allow_comments');
        $this->value['hidden'] = $db->f('hidden');
        $this->value['cc_license'] = $this->cc_license[$this->cmssite->get('cc_license')];
        $this->value['site']['url'] = $this->cmssite->get('url');

        $this->template->id = $db->f('template_id');
        $this->template->load();

        $this->value['template_id'] = $db->f('template_id');
        $this->value['template_identifier'] = $this->template->get('identifier');

        if ($this->get('type') == 'page') {
            $i = 0;
            $page_tree[$i]['navigation_name'] = $this->get('navigation_name');
            $page_tree[$i]['url'] = $this->get('url');
            $page_tree[$i]['url_self'] = $this->get('url_self');
            $page_tree[$i]['id'] = $this->get('id');

            $child_of_id = $this->get('child_of_id');
            while($child_of_id != 0) {
                $i++;

                $db->query("SELECT child_of_id, navigation_name, title, id, identifier FROM cms_page WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND active = 1 AND type_key = ".$this->get('type_key')." AND id = ".$child_of_id);
                if ($db->nextRecord()) {
                    $page_tree[$i]['navigation_name'] = $db->f('navigation_name');
                    if (empty($page_tree[$i]['navigation_name'])) {
                        $page_tree[$i]['navigation_name'] = $db->f('title');
                    }
                    $page_tree[$i]['url'] = $this->cmssite->get('url').$db->f('identifier').'/';
                    $page_tree[$i]['url_self'] = $db->f('identifier').'/';
                    $page_tree[$i]['id'] = $db->f('id');
                    $child_of_id = $db->f('child_of_id');

                } else {
                    $child_of_id = 0;
                }

                if ($i == 50) {
                    trigger_error("The while loop is runing loose in CMS_Page::load", E_USER_ERROR);
                }
            }

            // Vi vender arrayet rundt, s� key kommer til at passe til level.
            $this->value['page_tree'] = array_reverse($page_tree);
        }

        return true;
    }

    /**
     * Checks whether the correct sections has been created on the page.
     * Maybe a bit ot much that it is checked each time
     */
    function getSections()
    {
        $template_sections = $this->template->getSections();

        foreach ($template_sections as $template_section) {
            $db = new DB_Sql;
            $db->query("SELECT id FROM cms_section WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND page_id = ".$this->get('id')." AND site_id = ".$this->cmssite->get('id')." AND template_section_id = " . $template_section['id']);

            // opretter de sektioner der ikke er oprettet p� siden
            if (!$db->nextRecord()) {
                $section = CMS_Section::factory($this, 'type', $template_section['type']);
                $section->save(array('type_key' => $template_section['type_key'], 'template_section_id' => $template_section['id']));
            }

        }

        // man kan rende ind i det problem at en sektion er i overskud
        // jeg vil foresl�, at hvis userobjektet findes, s� tages den med og p� page.php
        // gives en mulighed for at slette den. Eksternt skal den ikke med

        $db = new DB_Sql;
        $db->query("SELECT cms_section.id FROM cms_section INNER JOIN cms_template_section ON cms_section.template_section_id = cms_template_section.id
            WHERE cms_section.intranet_id = ".$this->kernel->intranet->get('id')."
                AND cms_section.page_id = " . $this->id . " ORDER BY cms_template_section.position ASC");
        $i = 0;
        $section = array();
        while ($db->nextRecord()) {
            $section[$i] = CMS_Section::factory($this, 'cmspage_and_id', $db->f('id'));
            $i++;
        }

        return $section;

    }

    // @todo dette navn giver ikke nogen mening
    function collect()
    {
        $sections = $this->getSections();
        $page_sections = array();
        $i = 0;
        if (is_array($sections) AND count($sections) > 0) {
            foreach ($sections AS $key => $section) {
                $page_sections[$i] = $section->get();
                $i++;
            }
        }
        return $page_sections;
    }

    /**
     * Funktion til at udskrive elementerne
     *

    function display($type = '') {
        $elements = $this->getElements();
        $display = '';


        if (is_array($elements) AND count($elements) > 0) {
            foreach ($elements AS $key => $element) {
                $display .= $element->display($type);
            }
        }


        return $display;
    }
    */
    function getComments()
    {
        if (!$this->kernel->intranet->hasModuleAccess('contact')) {
            return '';
        }

        $this->kernel->useShared('comment');

        $i = 0;
        $messages = Comment::getList('cmspage', $this->kernel, $this->get('id'));

        return $messages;
    }


    function getList()
    {
        $gateway = new Intraface_modules_cms_PageGateway($this->kernel, new DB_Sql);
        $gateway->setDBQuery($this->getDBQuery());
        return $gateway->findAllBySite($this->cmssite);

        /*
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
            $page_tree = $this->get('page_tree');
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

                $pages[$i]['status'] = $this->status[$cmspage[$n]->f('status_key')];

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
		*/
    }

    function getPicture($pic_id)
    {
        $shared_filehandler = $this->kernel->useShared('filehandler');
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
     * @todo is this still used after the introduction of publish and unpublish
     */
    function setStatus($status)
    {
        if (empty($status)) {
            $status = 'draft';
        }
        if (!in_array($status, $this->status)) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE cms_page SET status_key = " . array_search($status, $this->status) . " WHERE id = " . $this->id . " AND intranet_id = " . $this->cmssite->kernel->intranet->get('id'));
        $this->value['status_key'] = array_search($status, $this->status);
        return true;
    }

    function isLocked()
    {
        return 0;
    }

    function getKeywords()
    {
        return ($this->keywords = new Keyword($this));
    }

    function getKeywordAppender()
    {
        return new Intraface_Keyword_Appender($this);
    }

    /**
     *
     * Funktionen skal tjekke alle siderne igennem for at se, om der findes undersider -
     * ellers vil de forsvinde fra oversigten.
     */
    function delete()
    {
        $db = new DB_Sql();
        $db2 = new DB_Sql;
          // egentlig skuille denne m�ske v�re rekursiv?

        /*
        // I am not quite sure what this one is suppossed to do - see the next one instead.
        $sql = "SELECT * FROM cms_page WHERE child_of_id=" . $this->id . " AND site_id = " . $this->cmssite->get('id');
        $db->query($sql);
        while($db->nextRecord()) {
            $db2->query('UPDATE cms_page SET child_of_id = '.$db->f('child_of_id').' WHERE child_of_id = ' . $this->id . ' AND site_id = ' . $this->cmssite->get('id'));
        }
        */

        // WE move all subpages to a level under - this also works on recursive sites.
        // @todo: BUT it can be a mess and the position of the pages is not corrected
        $db->query('UPDATE cms_page SET child_of_id = '.intval($this->get('child_of_id')).' WHERE child_of_id = '.intval($this->id));

        $sql = "UPDATE cms_page SET active = 0 WHERE id=" . $this->id . " AND site_id = ".$this->cmssite->get('id');
        $db->query($sql);
        $this->value['active'] = 0;
        $this->load();
        return true;

    }

    function getId()
    {
        return $this->id;
    }

    /**
     * Returns the possible page types
     *
     * @return array possible page types
     */
    public function getTypes()
    {
        return Intraface_modules_cms_PageGateway::getTypes();
    }

    /**
     * Returns the possible page types but with a binary index
     *
     * @return array possible page types with binary index
     */
    static public function getTypesWithBinaryIndex()
    {
        return Intraface_modules_cms_PageGateway::getTypesWithBinaryIndex();
    }

    /**
     * Returns the possible page types in plural
     *
     * @return array page types in plural
     */
    static public function getTypesPlural()
    {
        return array(
            'page' => 'pages',
            'article' => 'articles',
            'news' => 'news');
    }

    function isPublished()
    {
        return ($this->get('status_key') == 1);
    }

    function getStatus()
    {
        return $this->status[$this->value['status_key']];
    }

    function publish()
    {
        $db = new DB_Sql;
        $db->query("UPDATE cms_page SET status_key = " . array_search('published', $this->status) . " WHERE id = " . $this->id . " AND intranet_id = " . $this->cmssite->kernel->intranet->get('id'));
        $this->value['status_key'] = 1;
        $this->load();
        return true;
    }

    function unpublish()
    {
        $db = new DB_Sql;
        $db->query("UPDATE cms_page SET status_key = " . array_search('draft', $this->status) . " WHERE id = " . $this->id . " AND intranet_id = " . $this->cmssite->kernel->intranet->get('id'));
        $this->value['status_key'] = 0;
        $this->load();
        return true;
    }

    function isActive()
    {
        return ($this->value['active'] == 1);
    }

}