<?php
class Intraface_modules_contact_ContactGateway
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
        return new Contact($this->kernel, $id);
    }

    function findByEmail($value)
    {
        $this->db->query("SELECT address.belong_to_id AS id FROM contact INNER JOIN address ON address.belong_to_id = contact.id WHERE address.email = '".$value."' AND contact.intranet_id = " . $this->kernel->intranet->get('id') . " AND address.active = 1 AND contact.active = 1");
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByCode($value)
    {
        $this->db->query("SELECT id FROM contact WHERE code  = '".$value."' AND contact.intranet_id = " . $this->kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByUsername($value)
    {
        $this->db->query("SELECT id FROM contact WHERE username  = '".$value['username']."' AND password  = '".$value['password']."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }

    function findByOpenId($value)
    {
        $this->db->query("SELECT id FROM contact WHERE openid_url  = '".$value."' AND contact.intranet_id = " . $kernel->intranet->get('id'));
        if (!$this->db->nextRecord()) {
            throw new Exception('Contact not found');
        }
        return $this->findById($this->db->f('id'));
    }
}