<?php
/**
 * @package Intraface_Todo
 */
require_once 'Intraface/Standard.php';

class Intraface_Project
{
    /**
     * @var object
     */
    public $user;

    /**
     * @var object
     */
    private $item;

    /**
     * @var object
     */
    public $value;

    /**
     * Constructor
     *
     * @param object  $user The user
     * @param integer $id   Id for the project
     *
     * @return string
     */
    function __construct($db, $user, $id = 0)
    {
        $this->db   = $db;
        $this->user = $user;
        $this->id   = (int) $id;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Gets user
     *
     * @return object
     */
    function getUser()
    {
        return $this->user;
    }

    /**
     * Gets items
     *
     * @param string $type
     *
     * @return array
     */
    private function getTasks()
    {
        $result = $this->db->query("SELECT id FROM project_task WHERE project_id =" . $this->db->quote($this->getId(), 'integer') . "  AND active = 1 ORDER BY name ASC");
        $tasks = array();
        while ($row = $result->fetchRow()) {
            $tasks[] = $this->getTask($row['id']);
        }
        return $tasks;
    }

    /**
     * Gets item
     *
     * @param integer $id Id for specific item
     *
     * @return array
     */
    public function getTask($id = 0)
    {
        require_once 'Intraface/modules/project/Task.php';
        return new Intraface_Project_Task($this, (int)$id);
    }

    /**
     * Gets item
     *
     * @return boolean
     */
    private function load()
    {
        $result = $this->db->query("SELECT id, name, description FROM project WHERE id = " . $this->db->quote($this->id, 'integer'));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->value['id']          = $row['id'];
            $this->value['name']        = $row['name'];
            $this->value['description'] = $row['description'];
            return true;
        }
        return false;
    }

    /**
     * Gets id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets name
     *
     * @return string
     */
    public function getName()
    {
        return $this->value['name'];
    }

    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->value['description'];
    }

    /**
     * Saves project
     *
     * @param array $var Vars to save
     *
     * @return integer
     */
    public function save($var)
    {
        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW()";
        } else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->db->quote($this->id, 'integer');
        }

        $result = $this->db->query($sql_type. " project SET description = ".$this->db->quote($var['description'], 'text').", name = ".$this->db->quote($var['name'], 'text').", date_updated=NOW(), intranet_id = " . $this->db->quote($this->user->getActiveIntranetId(), 'integer') . $sql_end);

        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($this->id == 0) {
            $this->id = $this->db->lastInsertId();
        }

        $this->load();

        return $this->id;
    }

    /**
     * Gets all todo lists
     *
     * @return string
     */
    public function getList()
    {
        $result = $this->db->query("SELECT * FROM todo_list
            WHERE intranet_id = " . $this->user->getActiveIntranetId());
        $todo = array();
        while ($row = $result->fetchRow()) {
            $todo[] = new Intraface_Project($this->db, $this->user, $row['id']);
        }
        return $todo;
    }
}