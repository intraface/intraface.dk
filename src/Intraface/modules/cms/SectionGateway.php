<?php
class Intraface_modules_cms_SectionGateway
{
    protected $class_prefix = 'Intraface_modules_cms_section_';
    protected $kernel;
    protected $db;

    function __construct($kernel, DB_Sql $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    function findByPageAndType($page, $type)
    {
        // validering p� value // kun v�re gyldige elementtyper
        // object skal vre cmspage
        $class = $this->class_prefix . ucfirst($page);
        return new $class($page);
    }

    function findById($id)
    {
        // skal bruge kernel og numerisk value
        $cms_module = $this->kernel->getModule('cms');
        $section_types = $cms_module->getSetting('section_types');

        $this->db->query("SELECT id, page_id, type_key FROM cms_section WHERE id = " . $id . " AND intranet_id = " . $this->kernel->intranet->get('id'));

        if (!$this->db->nextRecord()) {
            return false;
        }
        $class = $this->class_prefix . ucfirst($section_types[$this->db->f('type_key')]);
        return new $class(CMS_Page::factory($this->kernel, 'id', $this->db->f('page_id')), $this->db->f('id'));

    }

    function findByPageAndId($page, $id)
    {
        // skal bruge cmspage-object og numerisk value id
        $cms_module = $this->kernel->getModule('cms');
        $section_types = $cms_module->getSetting('section_types');

        $this->db->query("SELECT id, page_id, type_key FROM cms_section WHERE id = " . $id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            return false;
        }
        $class = $this->class_prefix . ucfirst($section_types[$this->db->f('type_key')]);
        return new $class($page, $this->db->f('id'));
    }

}