<?php
/**
 * @package Intraface_Todo
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/Standard.php';

class TodoItem extends Standard
{
    /**
     * @var object
     */
    private $todo;

    /**
     * @var array
     */
    public $value;

    /**
     * Constructor
     *
     * @param object  $todo Todo liste
     * @param integer $id   Id for item
     *
     * @return void
     */
    public function __construct($todo, $id = 0)
    {
        if (!is_object($todo)) {
            trigger_error('Todo kræver Kernel', E_USER_ERROR);
        }
        $this->todo = $todo;
        $this->id = (int) $id;

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
        $db = new Db_Sql;
        $db->query("SELECT * FROM todo_item WHERE id = " . $this->id);
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['item'] = $db->f('item');
            $this->value['status'] = $db->f('status');
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
     * @param integer $user_id User id to save
     *
     * @return integer
     */
    public function save($var, $user_id = 0)
    {
        if (empty($var)) return;
        $var = safeToDb($var);
        $user_id = intval($user_id);

        $db = new DB_Sql;

        if ($this->id == 0) {
            $db->query("SELECT position FROM todo_item WHERE todo_list_id = " . $this->todo->get('id') . " ORDER BY position DESC LIMIT 1");
            $db->nextRecord();
            $new_position = $db->f('position') + 1;
        }

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW(), position = " . $new_position;
        } else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }

        $db->query($sql_type. " todo_item SET intranet_id = ".$this->todo->kernel->intranet->get('id').", item = '".$var."', todo_list_id = ".(int)$this->todo->get('id').", responsible_user_id = " .$user_id. " " . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
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
        $db->query("UPDATE todo_item SET status = 1 WHERE id = " . $this->id);
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
        $db->query("UPDATE todo_item SET active = 0 WHERE id = " . $this->id);
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
        return new Ilib_Position($db, "todo_item", $this->id, "todo_list_id=".$this->todo->get('id')." AND status = 0", "position", "id");
    }
}