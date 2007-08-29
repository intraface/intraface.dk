<?php
/**
 * Weblogin
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'MDB2.php';

class Weblogin  {

	private $db;
	private $session_id;
	public $intranet;
	public $setting;

	/**
	 * @param $session_id
	 */
	function __construct($session_id = '') {
		$this->db = MDB2::singleton(DB_DSN);

		if (PEAR::isError($this->db)) {
			die($this->db->getMessage());
		}

		$this->session_id = $session_id;
	}

	function auth($type, $key) {
		if($type == 'private') {

			$result = $this->db->query("SELECT id FROM intranet WHERE private_key = " . $this->db->quote($key, 'text'));
			if(PEAR::isError($result)) {
				trigger_error($result->getUserInfo(), E_USER_ERROR);
			}
			if($result->numRows() == 0) {
				return false;
			}
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row['id'];

		}
		elseif($type == 'public') {

			$result = $this->db->query("SELECT id FROM intranet WHERE public_key = ".$this->db->quote($key, 'text'));
			if(PEAR::isError($result)) {
				trigger_error($result->getUserInfo(), E_USER_ERROR);
			}
			if($result->numRows() == 0) {
				return false;
			}
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row['id'];

		}
		else {
			trigger_error('Ugyldig type weblogin', E_USER_ERROR);
			return false;
		}

	}

	function get() {
		return $this->session_id;
	}
}


?>