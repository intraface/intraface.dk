<?php
/**
 * Gad vide om man kan logge enkelte personer ind?
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../common.php');
require_once('IXR/IXR.php');

class TodoServer extends IXR_Server {

	var $kernel;

	function TodoServer() {
		$this->IXR_Server(array(
				'todo.get' => 'this:get',
				'todo.setDone' => 'this:setDone',
				'todo.getList' => 'this:getList'
			)
		);
	}

  /**
	 * Tjekker om forespørgslen må foretages
	 *
	 * @param struct $credentials
	 * 	- intranet_id = integer
	 *  - key_code = session_id
	 * @return true ved succes ellers object med fejlen
	 */

	function checkCredentials($credentials) {
		if (count($credentials) != 1) {
			return new IXR_Error(-2, 'Der er et forkert antal argumenter i credentials');
		}
		if (empty($credentials['private_key'])) {
			return new IXR_Error(-2, 'Du skal skrive en kode');
		}

		$this->kernel = new Kernel('weblogin');
		$this->kernel->weblogin('private', $credentials['private_key'], md5(session_id()));

		if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') {
			return new IXR_Error(-2, 'Du har ikke adgang til intranettet');
		}

		$this->kernel->module('todo');

	}

	/**
	 * @return true ved succes ellers object med fejlen
	 */

	 function get($arg) {

		$credentials = $arg[0];
		$id = $arg[1];

   	if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$db = new DB_Sql;
		$db->query("SELECT * FROM todo_list WHERE id = ".$id." AND intranet_id = " . $this->kernel->intranet->get('id'));
		if (!$db->nextRecord()) {
	    	return new IXR_Error(-2, 'Der er ikke nogen liste');
		}

		$todo = new TodoList($this->kernel, $db->f('id'));

		return array('name'=>$todo->get('list_name'), 'items'=>$todo->item->getList('undone'));
	}

	function getList($arg) {
		$credentials = $arg[0];
		$contact_id = $arg[1];


   	if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$db = new DB_Sql;
		$db->query("SELECT todo_list.name, todo_list.id FROM todo_list
			INNER JOIN todo_contact ON todo_contact.list_id = todo_list.id
			WHERE todo_contact.contact_id = " . $contact_id . "
				AND todo_list.intranet_id=".$this->kernel->intranet->get('id'));
		$i = 0;
		$list = array();

		while ($db->nextRecord()) {
			$list[$i]['id'] = $db->f('id');
			$list[$i]['name'] = $db->f('name');
		}
		return $list;
	}

	function setDone($arg) {

		$credentials = $arg[0];
		$list_id = $arg[1];
		$id = $arg[2];

   	if (is_object($return = $this->checkCredentials($credentials))) {
			return $return;
		}

		$db = new DB_Sql;
		$db->query("SELECT * FROM todo_list WHERE id = " . $list_id . " AND intranet_id = " . $this->kernel->intranet->get('id'));
		if (!$db->nextRecord()) {
			return new IXR_Error(-2, 'Der er ikke nogen liste');
		}

		$todo = new TodoList($this->kernel, $db->f('id'));
		$todo->loadItem($id);
		return $todo->item->delete();
  }

}

$server = new TodoServer();
?>