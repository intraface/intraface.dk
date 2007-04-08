<?php
/**
 * Styrer hvilket intranet man arbejder i
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 * @version 002
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Address.php';

class Intranet extends Standard {
	public $address;
	public $value;
	public $id; // intranet id. HACK It has to be public othervise it can not be changed from IntranetMaintenance that inherit from this.
	private $db;
	protected $permissions;

	/**
	 * Init: checker og vælger aktiv intranet
	 *
	 * @param	(object)$user	User objektet skal bruges
	 */
	function __construct($id) {
		/*
		if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
			trigger_error('intranet needs kernel', E_USER_ERROR);
		}
		*/
		$this->id = intval($id);
		// $this->kernel = &$kernel;
		$this->db = MDB2::singleton(DB_DSN);
		$this->error = new Error();

		if(!$this->load()) {
			trigger_error('unknown intranet', E_USER_ERROR);
		}
	}


	function load() {
		$this->db = MDB2::singleton(DB_DSN);

		$result = $this->db->query("SELECT
				id,
				name,
				identifier,
				key_code,
				public_key,
				contact_id,
				private_key,
				pdf_header_file_id,
				maintained_by_user_id
			FROM intranet
			WHERE id = ".$this->db->quote($this->id, 'integer'));

		if(PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
		}

		if($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
			$this->value = $row;
			/*
			$this->value['id'] = $db->f('id');
			$this->value['name'] = $db->f('name');
			$this->value['identifier'] = $db->f('identifier');
			$this->value['key_code'] = $db->f('key_code');
			$this->value['public_key'] = $db->f('public_key');
			$this->value['contact_id'] = $db->f('contact_id');
			$this->value['private_key'] = $db->f('private_key');
			$this->value['pdf_header_file_id'] = $db->f('pdf_header_file_id'); // egentlig burde dette vel bare være en indstilling i settings?
			$this->value['maintained_by_user_id'] = $db->f('maintained_by_user_id');
			*/
			$this->address = Address::factory('intranet', $this->id);
			return $this->id;
		}
		else {
			// $this->address = Address::factory('intranet', 0);
			$this->id = 0;
			return 0;
		}
		$result->free();
	}

	function hasModuleAccess($module) {

		if(is_string($module)) {
			if (empty($this->modules)) {
				$result = $this->db->query("SELECT id, name FROM module WHERE active = 1");
				while($row = $result->fetchRow()) {
					$this->modules[$row['name']] = $row['id'];
				}
				$result->free();
			}

			if (!empty($this->modules[$module])) {
				$module_id = $this->modules[$module];
			}
			else {
				trigger_error('Ugyldig modulnavn '.$module, E_USER_ERROR);
			}
		}
		else {
			$module_id = intval($module);
		}

		if (!empty($this->permissions)) {
			if (!empty($this->permissions['intranet']['module'][$module_id]) AND $this->permissions['intranet']['module'][$module_id] == true) {
				return true;
			}
			return false;
		}


		$result = $this->db->query("SELECT module_id FROM permission WHERE intranet_id = ".$this->db->quote($this->id, 'integer')." AND user_id = 0");
		while ($row = $result->fetchRow()) {
			$this->permissions['intranet']['module'][$row['module_id']] = true;
		}
		$result->free();

		if (!empty($this->permissions['intranet']['module'][$module_id]) AND $this->permissions['intranet']['module'][$module_id] == true) {
			return true;
		}
		return false;
	}
}
?>
