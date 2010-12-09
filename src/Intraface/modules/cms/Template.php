<?php
/**
 * Template
 *
 * Denne klasse skal v�re en skabelon for den enkelte side. Skabelonen skal
 * indeholde oplysninger om, hvilke sektioner.
 *
 * I praksis betyder det, at man opretter en skabelon. N�r man har oprettet
 * skabelonen v�lger man hvilke sektioner, der skal v�re i skabelonen. N�r man
 * har oprettet nogle felter, kan man v�lge, hvilke elementer, der er mulige
 * i de enkelte felter. Det kan formentlig gemmes med serialize(array()).
 *
 * Der skal v�re en standardskabelon med en sektion - og htmlelementet.
 *
 * P� den enkelte skabelon skal der v�re mulighed for at tilf�je ekstra datafelter,
 * som m�ske kan knyttes med andre sider
 * til den enkelte side (dette kunne for �vrigt v�re en generel klasse).
 *
 * @package Intraface_CMS
 * @author   Lars Olesen <lars@legestue.net>
 * @version  2.0
 *
 */
class CMS_Template extends Intraface_Standard
{
    public $id;
    public $value;
    public $cmssite;
    public $kernel;
    protected $keywords;
    public $error;
    protected $position;

    function __construct($cmssite, $id = 0)
    {
        if (!is_object($cmssite)) {
            throw new Exception('CMS_Template::__construct need CMS_Site');
        }
        $this->cmssite = $cmssite;
        $this->kernel = $cmssite->kernel;
        $this->kernel->useShared('keyword');

        $this->id = (int)$id;
        $this->error = new Intraface_Error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Used by Keyword
     *
     * @see Keyword
     *
     * @return string
     */
    function identify()
    {
        return 'cms_template';
    }

    function getPosition($db)
    {
        return new Ilib_Position($db, 'cms_template', $this->id, 'site_id = ' . $this->cmssite->get('id'), 'id', 'position');
    }

    /**
     *
     */
    function factory($kernel, $type = 'id', $id)
    {
        switch ($type) {
            case 'id':
                $db = new DB_Sql;
                $db->query("SELECT site_id, id FROM cms_template WHERE id = " . $id . " AND intranet_id = " . $kernel->intranet->get('id'));
                if (!$db->nextRecord()) {
                    return false;
                }

                $cmssite = new CMS_Site($kernel, $db->f('site_id'));

                return new CMS_Template($cmssite, $id);
            break;
            default:
                throw new Exception('CMS_Template::factory: duer ikke');
            break;
        }
    }


    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name, site_id, identifier, for_page_type FROM cms_template WHERE intranet_id = " . $this->cmssite->kernel->intranet->get('id') . " AND id = " . $this->id);

        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value['id'] = $db->f('id');
        $this->value['name'] = $db->f('name');
        $this->value['site_id'] = $db->f('site_id');
        $this->value['identifier'] = $db->f('identifier');
        $this->value['for_page_type'] = $db->f('for_page_type');

        return 1;
    }

    function validate($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isString($var['name'], 'error in name');
        $validator->isString($var['identifier'], 'error in identifier');
        if (!$this->isIdentifierUnique($var['identifier'])) {
            $this->error->set('error in identifier - has to be unique');
        }
        if (empty($var['for_page_type_integer'])) {
            $this->error->set('you should select at least one page type that the template is used on');
        }

        if ($this->error->isError()) {
            return 0;
        }

        return 1;
    }


    function save($var)
    {
        if (!empty($var['for_page_type']) && is_array($var['for_page_type'])) {
            $var['for_page_type_integer'] = array_sum($var['for_page_type']);
        } else {
            $var['for_page_type_integer'] = 0;
        }

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql;
        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        }
        $db->query($sql_type . " cms_template SET
            name = '".safeToDb($var['name'])."',
            date_updated = NOW(),
            intranet_id = ".safeToDb($this->cmssite->kernel->intranet->get('id')).",
            site_id = ".safeToDb($this->cmssite->get('id')).",
            identifier = '".safeToDb($var['identifier'])."',
            for_page_type = ".$var['for_page_type_integer']."
            " . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
            $this->getPosition($db)->moveToMax($this->id);

        }

        $this->load();

        return $this->id;
    }

    function getList($for_page_type = NULL)
    {
        $db = new DB_Sql;
        if (is_int($for_page_type)) {
            $sql_extra = 'for_page_type & '.intval($for_page_type).' AND';
        } else {
            $sql_extra = '';
        }

        $db->query("SELECT id, name, identifier, for_page_type FROM cms_template WHERE ".$sql_extra." intranet_id = " . $this->cmssite->kernel->intranet->get('id') . " AND site_id = " . $this->cmssite->get('id') . " AND active = 1 ORDER BY name");
        $i = 0;
        $templates = array();
        while ($db->nextRecord()) {
            $templates[$i]['id'] = $db->f('id');
            $templates[$i]['name'] = $db->f('name');
            $templates[$i]['identifier'] = $db->f('identifier');
            $templates[$i]['for_page_type'] = $db->f('for_page_type');
            //$templates[$i]['sections'] = count($this->getSections());
            $i++;
        }
        return $templates;
    }

    function getSections()
    {
        $this->section = new CMS_TemplateSection($this);
        return ($sections = $this->section->getList());
    }

    function getKeywords()
    {
        return($this->keywords = new Keyword($this));
    }

    function getKeywordAppender()
    {
        return new Intraface_Keyword_Appender($this);
    }

    function delete()
    {
        $db = new DB_Sql;
        $db->query("UPDATE cms_template SET active = 0 WHERE id = " . $this->id);
        return true;
    }

    function isIdentifierUnique($identifier)
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM cms_template WHERE site_id = " . $this->cmssite->get('id') . " AND identifier = '".$identifier."' AND active = 1 AND id != " . $this->id);
        if ($db->numRows() == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getId()
    {
        return $this->id;
    }
}