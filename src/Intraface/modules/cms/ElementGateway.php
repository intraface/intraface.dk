<?php
class Intraface_modules_cms_ElementGateway
{
    protected $class_prefix = 'Intraface_modules_cms_element_';
    protected $db;

    function __construct(DB_Sql $db)
    {
        $this->db = $db;
    }

    function findBySectionAndType($section, $type)
    {
        $class = $this->class_prefix . ucfirst($type);
        return new $class($section);
    }

    function findByKernelAndId($kernel, $id)
    {
        $cms_module = $kernel->getModule('cms');
        $element_types = $cms_module->getSetting('element_types');

        $this->db->query("SELECT id, section_id, type_key FROM cms_element WHERE id = " . $id . " AND intranet_id = " . $kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            return false;
        }
        $class = $this->class_prefix . ucfirst($element_types[$this->db->f('type_key')]);
        if (!class_exists($class)) {
            return false;
        }
        return new $class(CMS_Section::factory($kernel, 'id', $this->db->f('section_id')), $this->db->f('id'));
    }

    function findBySectionAndId($section, $id)
    {
        // FIXME - jeg tror den her kan skabe en del
        // af problemerne med mange kald
        // skal bruge cmspage-object og numerisk value id
        $cms_module = $section->kernel->getModule('cms');
        $element_types = $cms_module->getSetting('element_types');

        $this->db->query("SELECT id, section_id, type_key FROM cms_element WHERE id = " . $id . " AND intranet_id = " . $section->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            return false;
        }

        $class = $this->class_prefix . ucfirst($element_types[$this->db->f('type_key')]);
        if (!class_exists($class)) {
            return false;
        }

        return new $class($section, $this->db->f('id'));
    }
}