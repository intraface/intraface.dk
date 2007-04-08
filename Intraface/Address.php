<?php

/**
 * Styrer adresser til intranet, bruger, kunde og kontaktperson
 *
 * Klassen kan styrer flere forskellige typer af adresser. Både for intranettet, brugere, kunder og kontaktpersoner.
 * Beskrivelsen af hvilke og med hvilket navn er beskrevet længere nede.
 *
 * @todo Skal vi programmere intranet_id ind i klassen? Det kræver at den får Kernel.
 *
 * @version 001
 * @author Sune
 */

require_once 'Standard.php';
require_once '3Party/Database/DB_Sql.php';

class Address extends Standard {

	var $kernel;
	var $belong_to_key;
	var $belong_to_id;
	var $id;
	var $value = array();
	var $fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');


	/*
	var $user;

	var $address_id;
	;
	var $old_address_id;
	*/

	function address($id) {
		this::__construct($id);

	}


	/**
	 * Init: loader klassen
	 *
	 * Her er angivet de typer af adresser den kan håndtere med arrayet address_type[].
	 * $this-fields er felter i tabellen (db) som overføres til array og omvendt. Måske disse
	 * engang skal differencieres, så man angvier hvad feltet i tabellen skal svare til navnet i arrayet.
	 * Klassen loader også adressens felter
	 *
	 * @param (object)$kernel	kernel
	 * @param	(int)$id	Id on address.
	 */
	function __construct($id) {
		/*
		if(!is_object($kernel) || strtolower(get_class($kernel)) != 'kernel') {
			trigger_error("First parameter to Address should be kernel", E_USER_ERROR);
		}
		*/
		// $this->kernel = & Kernel::singleton();
		$this->id = $id;
		$this->load();

		$this->belong_to_types = $this->getBelongToTypes();

	}

	/**
	 *  factoy
	 *
	 * Returns an instace of Address from belong_to and belong_to_id
	 *
	 * @param (object)$kernel	kernel
	 * @param	(string)$belong_to	what the address belongs to, corresponding to the ones in Address::getBelongToTypes()
	 * @param	(int)$id id	from belong_to. NB not id on the address
	 * @return	(object)	Address
	 */

	function factory($belong_to, $belong_to_id) {

		// $kernel = new Kernel;
		$belong_to_types = Address::getBelongToTypes();

		$belong_to_key = array_search($belong_to, $belong_to_types);
		if($belong_to_key === false) {
			trigger_error("Invalid address type '".$belong_to."' in Address::factory", E_USER_ERROR);
		}

		settype($belong_to_id, 'integer');
		if($belong_to_id == 0) {
			trigger_error("Invalid belong_to_id in Address::factory", E_USER_ERROR);
		}



		$db = new DB_Sql;
		// intranet_id = ".$kernel->intranet->get('id')." AND
		$db->query("SELECT id FROM address WHERE type = ".$belong_to_key." AND belong_to_id = ".$belong_to_id." AND active = 1");
		if($db->numRows() > 1) {
			trigger_error('There is more than one active address for '.$belong_to.':'.$belong_to_id.' in Address::facotory', E_USER_ERROR);
		}
		if($db->nextRecord()) {
			return new Address($db->f('id'));
		}
		else {
			$address = new Address(0);
			$address->setBelongTo($belong_to, $belong_to_id);
			return $address;
		}
	}

	function getBelongToTypes() {

		return array(1 => 'intranet',
			2 => 'user',
			3 => 'contact',
			4 => 'contact_delivery',
			5 => 'contact_invoice',
			6 => 'contactperson');
	}


	function setBelongTo($belong_to, $belong_to_id) {

		if($this->id != 0) {
			// is id already set, then you can not change belong_to
			return;
		}

		$belong_to_types = $this->getBelongToTypes();
		$this->belong_to_key = array_search($belong_to, $belong_to_types);
		if($this->belong_to_key === false) {
			trigger_error("Invalid address type ".$belong_to." in Address::setBelongTo()", E_USER_ERROR);
		}

		$this->belong_to_id = (int)$belong_to_id;
		if($this->belong_to_id == 0) {
			trigger_error("Invalid belong_to_id in Address::setBelongTo()", E_USER_ERROR);
		}

	}



	/*
	function _old_Address($type, $id, $old_address_id = 0) {

		$this->db = new Db_sql;
		$this->id = (int)$id;
		$this->old_address_id = (int)$old_address_id;

		$address_type[1] = 'intranet';
		$address_type[2] = 'user';
		$address_type[3] = 'contact';
		$address_type[4] = 'contact_delivery';
		$address_type[5] = 'contact_invoice';
		$address_type[6] = 'contactperson';

		// $this->fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'contactname', 'ean');
		$this->fields = array('name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean');

		if($i = array_search($type, $address_type)) {
			$this->type = $i;
		}
		else {
			trigger_error('Ugyldig address type', FATAL);
		}

		return($this->address_id = $this->load());
	}
	*/


	/**
	 * Private: Loader data ind i array
	 */
	function load() {
		if ($this->id == 0) { return 0; }

		$db = MDB2::singleton(DB_DSN);
		$result = $db->query("SELECT id, type, belong_to_id, ".implode(', ', $this->fields)." FROM address WHERE id = ".(int)$this->id);

		if (PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
		}

		if($result->numRows() > 1) {
			trigger_error('There is more than one active address', E_USER_ERROR);
		}

		if($result->numRows() == 0) {
			$this->id = 0;
			$this->value['id'] = 0;

			return 0;
		}
		$row = $result->fetchRow();


		$this->value = $row;
		$this->value['address_id'] = $this->value['id'] = $row['id'];
		$this->belong_to_key = $row['type'];
		$this->belong_to_id = $row['belong_to_id'];

		return $this->id;
	}

	/**
	 * Public: Denne funktion gemmer data. At gemme data vil sige, at den gamle adresse gemmes, men den nye aktiveres.
	 *
	 * @param	(array)$array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
	 * $return	(int)	Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme på en old_address.
	 */
	function save($array_var) {

		if($this->belong_to_key == 0 || $this->belong_to_id == 0) {
			trigger_error("belong_to or belong_to_id was not set. Maybe because the provided address id was not valid. In Address::save", E_USER_ERROR);
		}

		$db = MDB2::singleton(DB_DSN);
		$sql = '';

		if(count($array_var) > 0) {
			if($this->id != 0) {
				$do_update = 0;
				foreach($this->fields AS $i => $field) {
					if(array_key_exists($field, $array_var) AND isset($array_var[$field])) {
						$sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
						if($this->get($field) != $array_var[$field]) {
							$do_update = 1;
						}
					}
				}
			}
			else {
				// Kun hvis der rent faktisk gemmes nogle værdier opdaterer vi. hvis count($arra_var) > 0 så må der også være noget at opdatere?
				$do_update = 0;
				foreach($this->fields AS $i => $field) {
					if(array_key_exists($field, $array_var) AND isset($array_var[$field])) {
						$sql .= $field.' = "'.safeToDb($array_var[$field]).'", ';
						$do_update = 1;
					}
				}
			}



			if($do_update == 0) {
				// There is nothing to save, but that is OK, so we just return 1
				return 1;
			}
			else {
				$db->exec("UPDATE address SET active = 0 WHERE type = ".$this->belong_to_key." AND belong_to_id = ".$this->belong_to_id);
				$db->exec("INSERT INTO address SET ".$sql." type = ".$this->belong_to_key.", belong_to_id = ".$this->belong_to_id.", active = 1, changed_date = NOW()");
				$this->id = $db->lastInsertId('address', 'id');
				$this->load();
				return 1;
			}
		}
		else {
			// Der var slet ikke noget indhold i arrayet, så vi lader være at opdatere, men siger, at vi gjorde.
			return 1;
		}
	}

	/**
	 * Public: Opdatere en adresse.
	 *
	 * Denne funktion overskriver den nuværende adresse. Benyt som udagangspunkt ikke denne, da historikken på adresser skal gemmes.
	 *
	 * @param	(array)$array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
	 * $return	(int)	Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme på en old_address.
	 */
	function update($array_var) {
		if($this->id == 0) {
			trigger_error("is has to be set to use Address::update, maybe you want to use Address::save IN Address->update", E_USER_ERROR);
		}

		foreach($this->fields AS $i => $field) {
			$sql = '';
			if(isset($array_var[$field])) {
				$sql .= $field." = ".$db->quote($array_var[$field]).", ";
			}
		}

		$db = MDB2::singleton(DB_DSN);
		$db->exec("UPDATE address SET ".$sql." changed_date = NOW() WHERE id = ".$this->id);
		$this->load();
		return 1;
	}
}
?>
