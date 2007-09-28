<?php
/**
 * @package Intraface_Todo
 * @author Lars Olesen <lars@legestue.net>
 */

class TodoItem extends Standard {

    var $todo;
    var $value;

    function TodoItem(& $todo, $id = 0) {
        if (!is_object($todo) OR strtolower(get_class($todo)) != 'todolist') {
            trigger_error('Todo kræver Kernel', FATAL);
        }
        $this->todo = & $todo;
        $this->id = (int) $id;

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     * @access private
     */
    function load() {
        $db = new Db_Sql;
        $db->query("SELECT * FROM todo_item WHERE id = " . $this->id . " LIMIT 1");
        if ($db->nextRecord()) {
            $this->value['id'] = $db->f('id');
            $this->value['item'] = $db->f('item');
            $this->value['status'] = $db->f('status');
            return 1;
        }
    return 0;

  }

    function getList($type="all") {
        if ($type == "undone") {
            $sql_status = "status = 0 AND";
        }
        else {
            $sql_status = "status >= 0 AND";
        }

        $db = new DB_Sql;
        $db->query("SELECT * FROM todo_item WHERE " . $sql_status . " todo_list_id =" . (int)$this->todo->get('id') . "  AND active = 1 ORDER BY status ASC, position ASC");
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

    function save($var, $user_id = 0) {
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
        }
        else {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        }

        $db->query($sql_type. " todo_item SET intranet_id = ".$this->todo->kernel->intranet->get('id').", item = '".$var."', todo_list_id = ".(int)$this->todo->get('id').", responsible_user_id = " .$user_id. " " . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        return $this->id;
    }

    function setDone() {
        if ($this->id == 0) {
            return 0;
        }
       $db = new DB_Sql;
        $db->query("UPDATE todo_item SET status = 1 WHERE id = " . $this->id);
        return 1;
    }

    function setAllUndone() {
       $db = new DB_Sql;
        $db->query("UPDATE todo_item SET status = 0 WHERE todo_list_id = " . $this->todo->get('id'));
        return 1;
    }

    function delete() {
        if ($this->id <= 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("UPDATE todo_item SET active = 0 WHERE id = " . $this->id);
        return 1;
    }

    function moveUp() {
        $position = new Position("todo_item", "todo_list_id=".$this->todo->get('id')." AND status = 0", "position", "id");
        $position->moveUp($this->id);
    }

    function moveDown() {
        $position = new Position("todo_item", "todo_list_id=".$this->todo->get('id')." AND status = 0", "position", "id");
        $position->moveDown($this->id);
    }
}
?>
