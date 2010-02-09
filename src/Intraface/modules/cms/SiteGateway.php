<?php
class Intraface_modules_cms_SiteGateway
{
    protected $db;
    protected $kernel;

    function __construct($kernel, DB_Sql $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
    }

    function findById($id)
    {
        return new CMS_Site($this->kernel, $id);
    }

    function getEmptySite()
    {
        return new CMS_Site($this->kernel);
    }

    function getAll()
    {
        $this->db->query("SELECT id, name FROM cms_site WHERE intranet_id = " . $this->kernel->intranet->get('id'). " AND active = 1");
        $i = 0;
        $sites = array();
        while ($this->db->nextRecord()) {
            $sites[$i]['id'] = $this->db->f('id');
            $sites[$i]['name'] = $this->db->f('name');
            $i++;
        }
        return $sites;
    }
}