<?php
/**
 * Template
 *
 * Denne klasse skal være en skabelon for den enkelte side. Skabelonen skal
 * indeholde oplysninger om, hvilke sektioner.
 *
 * I praksis betyder det, at man opretter en skabelon. Når man har oprettet
 * skabelonen vælger man hvilke sektioner, der skal være i skabelonen. Når man
 * har oprettet nogle felter, kan man vælge, hvilke elementer, der er mulige
 * i de enkelte felter. Det kan formentlig gemmes med serialize(array()).
 *
 * Der skal være en standardskabelon med en sektion - og htmlelementet.
 *
 * På den enkelte skabelon skal der være mulighed for at tilføje ekstra datafelter,
 * som måske kan knyttes med andre sider
 * til den enkelte side (dette kunne for øvrigt være en generel klasse).
 *
 * @package Intraface_CMS
 * @author   Lars Olesen <lars@legestue.net>
 * @version  2.0
 *
 */
require_once 'Intraface/Standard.php';

class CMS_Template extends Standard
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
            trigger_error('CMS_Template::__construct need CMS_Site', E_USER_ERROR);
        }
        $this->cmssite = $cmssite;
        $this->kernel = $cmssite->kernel;
        $this->kernel->useShared('keyword');

        $this->id = (int)$id;
        $this->error = new Error;

        if ($this->id > 0) {
            $this->load();
        }
    }

    function getPosition($db)
    {
        require_once 'Ilib/Position.php';
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
                trigger_error('CMS_Template::factory: duer ikke');
            break;
        }
    }


    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name, site_id, identifier FROM cms_template WHERE intranet_id = " . $this->cmssite->kernel->intranet->get('id') . " AND id = " . $this->id);

        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value['id'] = $db->f('id');
        $this->value['name'] = $db->f('name');
        $this->value['site_id'] = $db->f('site_id');
        $this->value['identifier'] = $db->f('identifier');

        return 1;
    }

    function validate($var)
    {
        $validator = new Validator($this->error);
        $validator->isString($var['name'], 'error in name');
        $validator->isString($var['identifier'], 'error in identifier');
        if (!$this->isIdentifierUnique($var['identifier'])) {
            $this->error->set('error in identifier - has to be unique');
        }
        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }


    function save($var)
    {
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
            identifier = '".safeToDb($var['identifier'])."'
        " . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
            $this->getPosition($db)->moveToMax($this->id);

        }

        $this->load();

        return $this->id;
    }

    function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name, identifier FROM cms_template WHERE intranet_id = " . $this->cmssite->kernel->intranet->get('id') . " AND site_id = " . $this->cmssite->get('id') . " AND active = 1 ORDER BY name");
        $i = 0;
        $templates = array();
        while ($db->nextRecord()) {
            $templates[$i]['id'] = $db->f('id');
            $templates[$i]['name'] = $db->f('name');
            $templates[$i]['identifier'] = $db->f('identifier');
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
        return 1;
    }

    function isIdentifierUnique($identifier)
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM cms_template WHERE site_id = " . $this->cmssite->get('id') . " AND identifier = '".$identifier."' AND active = 1 AND id != " . $this->id);
        if ($db->numRows() == 0) return 1;
    }

    function getId()
    {
        return $this->id;
    }
}