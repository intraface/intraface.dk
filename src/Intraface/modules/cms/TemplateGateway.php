<?php
class Intraface_modules_cms_TemplateGateway
{
    protected $kernel;
    protected $db;

    function __construct($kernel, DB_Sql $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    function findById($id)
    {
                $this->db->query("SELECT site_id, id FROM cms_template WHERE id = " . $id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
                if (!$this->db->nextRecord()) {
                    return false;
                }

                $cmssite = new CMS_Site($this->kernel, $this->db->f('site_id'));

                return new CMS_Template($cmssite, $id);
    }
}