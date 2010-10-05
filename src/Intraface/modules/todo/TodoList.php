<?php
/**
 * @package Intraface_Todo
 */
class TodoList extends Intraface_Standard
{
    /**
     * @var object
     */
    public $kernel;

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
     * @param object  $kernel Kernel object
     * @param integer $id     Id for todo list
     *
     * @return string
     */
    function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            throw new Exception('Todo kr�ver Kernel');
        }

        $this->kernel = $kernel;
        $this->id = (int) $id;

        if ($this->id > 0) {
            $this->load();
        } else {
            $this->item = $this->loadItem();
        }
    }

    /**
     * Gets items
     *
     * @param string $type
     *
     * @return array
     */
    private function getItems($type)
    {
        if ($type == "undone") {
            $sql_status = "status = 0 AND";
        } else {
            $sql_status = "status >= 0 AND";
        }

        $db = new DB_Sql;
        $db->query("SELECT * FROM todo_item WHERE " . $sql_status . " todo_list_id =" . (int)$this->getId() . "  AND active = 1 ORDER BY status ASC, position ASC");
        $ids = array();
        $i = 0;
        while ($db->nextRecord()) {
            $ids[$i]['id'] = $db->f('id');
            $ids[$i]['item'] = $db->f('item');
            $ids[$i]['status'] = $db->f('status');
            $ids[$i]['responsible_user_id'] = $db->f('responsible_user_id');
            $i++;
        }
        return $ids;
    }

    /**
     * Gets items
     *
     * @return array
     */
    public function getUndoneItems()
    {
        return $this->getItems('undone');
    }

    /**
     * Gets items
     *
     * @return array
     */
    function getAllItems()
    {
        return $this->getItems('all');
    }

    /**
     * Gets items
     *
     * @return boolean
     */
    public function setAllItemsUndone()
    {
        $db = new DB_Sql;
        $db->query("UPDATE todo_item SET status = 0 WHERE todo_list_id = " . $this->getId());
        return true;
    }

    /**
     * Gets item
     *
     * @param integer $id Id for specific item
     *
     * @return array
     */
    public function getItem($id = 0)
    {
        require_once 'Intraface/modules/todo/TodoItem.php';
        return new TodoItem($this, (int)$id);
    }

    /**
     * Gets item
     *
     * @param integer $id Id for specific item
     *
     * @deprecated
     *
     * @return array
     */
    private function loadItem($id = 0)
    {
        require_once 'Intraface/modules/todo/TodoItem.php';
        return ($this->item = new TodoItem($this, (int)$id));
    }

    /**
     * Deletes all items
     *
     * @return boolean
     */
    public function deleteAllItems()
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM todo_item WHERE todo_list_id = " . $this->id. " AND active = 1 AND status = 0");
        return true;
    }

    /**
     * Gets item
     *
     * @return boolean
     */
    private function load()
    {
        $db = new Db_Sql;
        $db->query("SELECT * FROM todo_list WHERE id = " . $this->id . " LIMIT 1");
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['list_name'] = $db->f('name');
            $this->value['list_description'] = $db->f('description');
            $this->value['public_key'] = $db->f('public_key');

            $this->item = $this->getItem();
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
     * Gets ListName
     *
     * @return string
     */
    public function getListName()
    {
        $this->value['list_name'];
    }

    /**
     * Gets list description
     *
     * @return string
     */
    public function getListDescription()
    {
        $this->value['list_description'];
    }

    /**
     * Gets public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        $this->value['public_key'];
    }

    /**
     * Saves todolist
     *
     * @param array $var Vars to save
     *
     * @return integer
     */
    public function save($var)
    {
        $var = safeToDb($var);

        if ($this->id == 0) {
            $sql_type = "INSERT INTO ";
            $sql_end = ", date_created = NOW(), public_key = '" .$this->kernel->randomKey(10) . "'";
        } else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }
        $db = new DB_Sql;
        $db->query($sql_type. " todo_list SET description = '".$var['list_description']."', name = '".$var['list_name']."', date_changed=NOW(),intranet_id = " . $this->kernel->intranet->get('id') . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        $this->load();

        return $this->id;
    }

    /**
     * Gets all todo lists
     *
     * @return string
     */
    public function getList($type = 'undone')
    {
        $db = new DB_sql;
        $db->query("SELECT * FROM todo_list
            WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        $ids = array();
        $i=0;
        while ($db->nextRecord()) {
            $todo = new TodoList($this->kernel, $db->f('id'));
            if ($type == 'done' and $todo->howManyLeft() > 0) {
                continue;
            } elseif ($type != 'done'  AND $todo->howManyLeft() == 0) {
                continue;
            }
            $ids[$i]['id'] = $db->f('id');
            $ids[$i]['name'] = $db->f('name');
            $ids[$i]['left'] = $todo->howManyLeft();
            $i++;
        }
        return $ids;
    }

    /**
     * Gets how many items left on list
     *
     * @return integer
     */
    public function howManyLeft()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM todo_item WHERE status = 0 AND active = 1 AND todo_list_id = " . $this->id);
        return $db->numRows();
    }

    /**
     * Adds a contact
     *
     * @return boolean
     */
    public function addContact($id)
    {
        $id = (int)$id;
        $db = new DB_Sql;
        $db->query("SELECT * FROM todo_contact WHERE contact_id = " . $id);
        if ($db->nextRecord()) {
            return true;
        }
        $db->query("INSERT INTO todo_contact SET contact_id = " . $id . ", list_id = ".$this->get('id').", intranet_id = " . $this->kernel->intranet->get('id'));
        return true;
    }

    /**
     * Gets the added contacts
     *
     * @return array
     */
    function getContacts()
    {
        $db = new DB_Sql;
        $i = 0;
        $contacts = array();
        $db->query("SELECT * FROM todo_contact WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        while ($db->nextRecord()) {
            $contacts[$i] = $db->f('contact_id');
            $i++;
        }
        return $contacts;
    }
}