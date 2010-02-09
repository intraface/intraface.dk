<?php
class Intraface_modules_cms_PageGateway
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
        $this->db->query("SELECT id, site_id FROM cms_page WHERE id = " . (int)$id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            return false;
        }
        $site = new CMS_Site($this->kernel, $this->db->f('site_id'));
        $object = new CMS_Page($site, (int)$id);
        return $object;
    }

    function findByIdentifier($identifier)
    {
        $identifier = strip_tags($identifier);

        if (!empty($identifier)) {
            $this->db->query("SELECT site_id, id FROM cms_page WHERE identifier = '" . $value['identifier'] . "' AND intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $value['site_id']);
        } else {
            // @todo choose the default page - vi skal lige have noget med publish og expire date her ogs�
            $this->db->query("SELECT site_id, id FROM cms_page WHERE intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND status_key = 1 AND site_id = " . $value['site_id'] . " ORDER BY position ASC LIMIT 1");
        }
        if (!$this->db->nextRecord()) {
            $this->db->query("SELECT site_id, id FROM cms_page WHERE id = " . (int)$value['identifier'] . " AND intranet_id = " . $kernel->intranet->get('id') . " AND active = 1 AND site_id = " . $value['site_id']);
            if (!$this->db->nextRecord()) {
                return false;
            }
        }
        return new CMS_Page(new CMS_Site($this->kernel, $this->db->f('site_id')), $this->db->f('id'));
    }

}