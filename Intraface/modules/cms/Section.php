<?php
/**
 * Der skal kun kunne være en section pr. template_section_id pr. side.
 *
 * TODO make this abstract so we make sure that the classes extending this does it correctly
 *
 * @package Intraface_CMS
 */
require_once('section/ShortText.php');
require_once('section/LongText.php');
require_once('section/Picture.php');
require_once('section/Mixed.php');
//require_once('FormFilter.php');

class CMS_Section extends Standard {

    var $id;
    private $db;
    var $cmspage;
    var $kernel;
    var $parameter;
    var $template_section;
    var $value;
    var $error;
    var $section_types;

    /**
     * Constructor:
     * Construktor skal enten have cmspage eller en kernel.
     * Hvis den får kernel skal den have et id.
     * Fordelen er, at man ikke behøver at vide hvilken side elementet hører til,
     * men blot behøver, at have elementid.
     */

    function __construct($cmspage, $id = 0) {
        /*
        if (!is_object($cmspage) OR strtolower(get_class($cmspage)) != 'cms_page') {
            trigger_error('Section::__construct needs CMS_Page - got ' . get_class($cmspage), E_USER_ERROR);
        }
        */

        $this->db = MDB2::singleton(DB_DSN);
        $this->cmspage = $cmspage;
        $this->kernel = $cmspage->kernel;
        $this->id = (int) $id;
        //$template_class = 'CMS_Template_' . $this->value['type'];

        $this->error = new Error();
        $this->value['identify_as'] = 'cms_section'; // bruges af parameter

        $cms_module = $this->cmspage->kernel->module('cms');
        $this->section_types = $cms_module->getSetting('section_types');

        if (is_string($this->value['type']) AND in_array($this->value['type'], $this->section_types)) {
            $this->value['type_key'] = array_search($this->value['type'], $this->section_types);
        }

        $this->parameter = $this->createParameter();

        if ($this->id > 0) {
            $this->load();
            if (method_exists($this, 'load_section')) {
                $this->load_section();
            }
        }

    }

    function createParameter() {
        return new CMS_Parameter($this);
    }

    function addParameter($key, $value) {
        return $this->parameter->save($key, $value);
    }

    function factory(& $object, $type, $value) {
        $class_prefix = 'CMS_Section_';
        switch ($type) {
            case 'type':
                // validering på value // kun være gyldige elementtyper
                // object skal vre cmspage
                $class = $class_prefix . $value;
                return new $class($object);
                break;
            case 'id':

                // skal bruge kernel og numerisk value
                $cms_module = $object->getModule('cms');
                $section_types = $cms_module->getSetting('section_types');

                $db = new DB_Sql;
                $db->query("SELECT id, page_id, type_key FROM cms_section WHERE id = " . $value . " AND intranet_id = " . $object->intranet->get('id'));

                if (!$db->nextRecord()) {
                    return false;
                }

                $class = $class_prefix . $section_types[$db->f('type_key')];
                return new $class(CMS_Page::factory($object, 'id', $db->f('page_id')), $db->f('id'));

                break;
            case 'cmspage_and_id':
                // skal bruge cmspage-object og numerisk value id
                $cms_module = $object->kernel->getModule('cms');
                $section_types = $cms_module->getSetting('section_types');

                $db = new DB_Sql;
                $db->query("SELECT id, page_id, type_key FROM cms_section WHERE id = " . $value . " AND intranet_id = " . $object->kernel->intranet->get('id'));
                if (!$db->nextRecord()) {
                    return false;
                }
                $class = $class_prefix . $section_types[$db->f('type_key')];
                return new $class($object, $db->f('id'));

                break;
            default:
                trigger_error('Section::factory() type not known', E_USER_ERROR);
                break;
        }
    }

    function load() {

        if ($this->id == 0) {
            return 0;
        }


        $result = $this->db->query("SELECT
                cms_section.page_id,
                cms_section.type_key,
                cms_section.template_section_id,
                cms_section.id
            FROM cms_section
            LEFT JOIN cms_template_section
                ON cms_section.template_section_id = cms_template_section.id
            WHERE cms_section.intranet_id = ".$this->cmspage->kernel->intranet->get('id')."
                AND cms_section.id = " . $this->id);
        if (!$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            return 0;
        }

        $this->value = array_merge($this->value, $row);
        $this->value['type'] = $this->section_types[$this->get('type_key')];
        $this->template_section = $this->createTemplateSection($this->get('template_section_id'));
        $this->value['section_name'] = $this->template_section->get('name');
        $this->value['section_identifier'] = $this->template_section->get('identifier');

        return $this->id;
    }

    function createTemplateSection($template_section_id) {
        return CMS_TemplateSection::factory($this->kernel, 'id', $template_section_id);
    }

    function validate($var) {
        /*
        $validator = new Validator($this->error);
        $validator->isNumeric($var['type_key'], 'type_key');
        $validator->isNumeric($var['template_section_id'], 'template_section_id');
        if ($this->error->isError()) {
            return 0;
        }
        */
        return 1;
    }

    function save($var) {
        if (!$this->validate($var)) {
            return 0;
        }
        $db = new DB_Sql;

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW(),
                type_key = ".$var['type_key'] . ",
                template_section_id = ".$var['template_section_id'];
        }
        else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }
        $sql = $sql_type . " cms_section SET
                intranet_id = ".$this->cmspage->kernel->intranet->get('id').",
                page_id=". (int)$this->cmspage->get('id') . ",
                site_id=". (int)$this->cmspage->cmssite->get('id') . ",
                date_updated = NOW()
            " . $sql_end;

        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        $this->load();

        $this->parameter = new CMS_Parameter($this);

        if (!$this->validate_section($var)) {
            return true;
        }

        if (!$this->save_section($var)) {
            return true;
        }

        return $this->id;
    }
}


?>