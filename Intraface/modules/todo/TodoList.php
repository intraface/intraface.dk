<?php
/**
 * @package Intraface_Todo
 */
require_once 'Intraface/Standard.php';

class TodoList extends Standard
{
    public $kernel;
    public $item;
    public $value;

    function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel)) {
            trigger_error('Todo kræver Kernel', E_USER_ERROR);
        }

        $this->kernel = $kernel;
        $this->id = (int) $id;

        if ($this->id > 0) {
            $this->load();
        } else {
            $this->item = $this->loadItem();
        }
    }

    function loadItem($id = 0)
    {
        require_once 'Intraface/modules/todo/TodoItem.php';
        return ($this->item = new TodoItem($this, (int)$id));
    }

    function deleteAllItems()
    {
        $db = new DB_Sql;
        $db->query("DELETE FROM todo_item WHERE todo_list_id = " . $this->id. " AND active = 1 AND status = 0");
    }

    private function load()
    {
        $db = new Db_Sql;
        $db->query("SELECT * FROM todo_list WHERE id = " . $this->id . " LIMIT 1");
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['list_name'] = $db->f('name');
            $this->value['list_description'] = $db->f('description');
            $this->value['public_key'] = $db->f('public_key');

            $this->item = $this->loadItem();
            return ($this->id = $db->f('id'));
        }
        return 0;
    }

    function save($var)
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

    function getList($type = 'undone')
    {
        $db = new DB_sql;
        $db->query("SELECT * FROM todo_list
            WHERE intranet_id = " . $this->kernel->intranet->get('id'));
        $ids = array();
        $i=0;
        while ($db->nextRecord()) {
            $todo = new TodoList($this->kernel, $db->f('id'));
            if($type == 'done' and $todo->howManyLeft() > 0) {
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

    function howManyLeft()
    {
        $db = new DB_Sql;
        $db->query("SELECT * FROM todo_item WHERE status = 0 AND active = 1 AND todo_list_id = " . $this->id);
        return $db->numRows();
    }

    function addContact($id)
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