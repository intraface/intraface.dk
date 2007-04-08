<?php
// not in use


/**
 * Låsen skal lige laves lidt om - den skal have en post id, og den
 * skal være i selve klasserne.
 */
class Lock {

	var $object;
	var $value;
	var $kernel;

	function Lock($object, $post_id = 0) {
		if (!is_object($object)) {
			trigger_error('Lock::Lock: Du skal bruge et object', FATAL);
		}
		$this->object = & $object;
		$this->post_id = (int)$post_id;

		switch(strtolower(get_class($this->object))) {

			case 'contact':
					$this->value['table'] = 'contact';
				break;
			default:
					trigger_error('Lock::Lock: Ikke gyldigt object', FATAL);
				break;

		}

		if (!is_object($object->kernel)) {
			trigger_error('Lock::Lock: Du skal bruge en kernel', FATAL);
		}

		$this->kernel = & $object->kernel;

	}

	/**
	 * Den enkelte bruger kan kun låse en post ad gangen.
	 */

	function lock_post($post_id) {
		if ($this->isLocked($post_id)) {
			return 0;
		}
		$db = new DB_Sql;
		$db->query("DELETE FROM lock_post WHERE user_id = " . $this->kernel->user->get('id'));

		$db->query("INSERT INTO lock_post
			SET
				user_id = " . $this->kernel->user->get('id') . ",
				date_created = NOW(),
				post_id = " . $post_id . ",
				table_name = '".$this->value['table'] . "'");
		return 1;
	}

	function unlock_post($post_id) {
		$db = new DB_Sql;

		$db->query("DELETE FROM lock_post WHERE user_id = " . $this->kernel->user->get('id'));
		return 1;
	}

	/**
	 * Spørgsmålet er hvor længe en låsning skal gemmes?
	 */

	function isLocked($post_id) {
		// HACK
		if (!is_object($this->kernel->user)) {
			return 0;
		}
		$timeout = 1; // HOUR
		$db = new DB_Sql;
		$db->query("SELECT * FROM lock_post WHERE DATE_SUB(NOW(), INTERVAL ".$timeout." HOUR) < date_created AND  table_name = '" . $this->value['table'] . "' AND post_id =" . $post_id . " AND user_id <> " . $this->kernel->user->get('id'));
		return $db->nextRecord();
	}

}

?>