<?php
/**
 * @package Intraface_Project
 * @author Lars Olesen <lars@legestue.net>
 */
class Intraface_Project_Task
{
    /**
     * @var object
     */
    private $project;

    /**
     * @var object
     */
    private $db;

    /**
     * @var array
     */
    private $value;

    /**
     * Constructor
     *
     * @param object  $db      Datbaseobject
     * @param object  $project Project
     * @param integer $id      Id for item
     *
     * @return void
     */
    public function __construct($db, $project, $id = 0)
    {
        $this->project = $project;
        $this->id      = (int) $id;
        $this->db      = $db;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Loads values into $value
     *
     * @return boolean
     */
    private function load()
    {
        $result = $this->db->query("SELECT item FROM project_task WHERE id = " . $this->db->quote($this->id, 'integer'));
        if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $this->value['item'] = $row['item'];
            return true;
        }
        return false;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->value['status'];
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getItem()
    {
        return $this->value['item'];
    }

    /**
     * Saves values
     *
     * @param array   $var     Values to save
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

        $result = $this->db->query($sql_type. " project_task SET intranet_id = ".$this->db->quote($this->project->getUser()->getActiveIntranetId(), 'integer').", item = ".$this->db->quote($var['item'], 'text').", project_id = ".$this->db->quote($this->project->getId(), 'integer') . $sql_end);

        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($this->id == 0) {
            $this->getPosition($this->db)->moveToMax();
            $this->id = $this->db->lastInsertId();
        }

        $this->load();

        return $this->id;
    }

    /**
     * Sets the item to done
     *
     * @return boolean
     */
    public function setDone()
    {
        if ($this->id == 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE project_task SET status = 1 WHERE id = " . $this->db->quote($this->id, 'integer'));
        return true;
    }

    /**
     * Deletes item
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->id <= 0) {
            return false;
        }
        $db = new DB_Sql;
        $db->query("UPDATE project_task SET active = 0 WHERE id = " . $this->db->quote($this->id, 'integer'));
        return true;
    }

    /**
     * Gets position object
     *
     * @param object $db Database object
     *
     * @return object
     */
    public function getPosition($db)
    {
        require_once 'Ilib/Position.php';
        return new Ilib_Position($db, "project_task", $this->id, "project_id=".$this->project->getId(), "position", "id");
    }
}