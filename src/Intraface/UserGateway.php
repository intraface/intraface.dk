<?php
ini_set('display_errors', true);
class Intraface_UserGateway
{
    protected $db;

    function __construct(DB_Sql $db)
    {
        $this->db = $db;
    }

    function findByUsername($id)
    {
        $result = $this->db->query('select id from user where email = "'.$id.'"');
        $this->db->nextRecord();
        return new Intraface_User($this->db->f('id'));
    }

    function findById($id)
    {
        return new Intraface_User($id);
    }

    function getAll()
    {
        $user = new Intraface_User();
        return $user->getAll();
    }
}