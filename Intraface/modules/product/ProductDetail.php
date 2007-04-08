<?php
/**
 * Styrer varer
 *
 * @version 001
 * @author Lars Olesen <lars@legestue.net>
 *
 * TODO Lige nu gemmer den altid en ny produktdetalje uanset, hvad jeg gør.
 */

 require_once('Intraface/tools/Amount.php');

class ProductDetail extends Standard {
	var $value = array();
	var $fields; // tabelfelter
	var $detail_id; // detalje_id :: burde nok laves om til $this->id;
	var $old_detail_id; // bruges kun til gamle produktdetaljer
	var $product; // produktobjekt
	var $db; // databaseobjekt

	/**
	 * Init: loader klassen
	 *
	 * @access Public
	 * @param	(int)$old_detail_id	Denne bruges kun, i det tilfælde, hvor man skal finde et gammelt produkt.
	 * @return	(int)	Returnerer 0 hvis produktet ikke er sat. Returnerer id på produktet hvis det er.
	 */
	function ProductDetail(& $product, $old_detail_id = 0) {
		if (!is_object($product) OR strtolower(get_class($product)) != 'product') {
			trigger_error('ProductDetail-objektet kræver et Product-objekt.', E_USER_ERROR);
		}
		$this->product = & $product;
		$this->db = new Db_sql;
		$this->old_detail_id = (int)$old_detail_id;

		$this->fields = array('number', 'name', 'description', 'price', 'unit', 'do_show', 'vat', 'weight', 'state_account_id');

		$this->detail_id = $this->load();
	}

	/**
	 * Private: Loader data ind i array
   *
   * @access private
   * @return produktdetalje id ved succes ellers 0
	 */
	function load() {
		if($this->old_detail_id != 0) {
			$sql = "id = ".$this->old_detail_id;
		}
		else {
			$sql = "active = 1";
		}

		$sql = "SELECT id, ".implode(',', $this->fields)." FROM product_detail WHERE ".$sql . "
			AND product_id = " . $this->product->get('id');
		$this->db->query($sql);
		if($this->db->numRows() > 1) {
			trigger_error('Systemfejl', 'Der er mere end en aktiv produktdetalje', E_USER_ERROR);
		}
		elseif($this->db->nextRecord()) {
			// hardcoded udtræk af nogle vigtige oplysnigner, som vi ikke kan have i feltlisten
			for($i = 0, $max = count($this->fields); $i<$max; $i++) {
				$this->value[$this->fields[$i]] = $this->db->f($this->fields[$i]);
			}

			// unit skal skrives om til den egentlige unit alt efter settings i produkterne
			$this->value['detail_id'] = $this->db->f('id');
			$this->value['unit_id'] = $this->db->f('unit');

			$module = $this->product->kernel->getModule('product');

			foreach ($module->getSetting('unit') AS $key=>$keyvalue) {
				if ($key == $this->db->f('unit')) {
					$this->value['unit'] = $keyvalue;
					$this->value['unit_key'] = $key;
				}
			}

     //********************************************************************
     // KIG PÅ HACK OVENOVER
     //*****************************************************************

			// udregne moms priser ud fra prisen, men kun hvis der er moms på den
			if ($this->db->f('vat') == 1) {
				$this->value['price_incl_vat'] = (float)$this->db->f('price') + ($this->db->f('price') * 0.25);
			}
			else {
				$this->value['price_incl_vat'] = (float)$this->db->f('price');
			}
			return $this->db->f('id');
		}
		else {
			return 0;
		}
	}

	function validate($array_var) {
		$validator = new Validator($this->product->error);
		$validator->isString($array_var['name'], 'Du har brugt ulovlige tegn i beskrivelsen');
		$validator->isString($array_var['description'], 'Du har brugt ulovlige tegn i beskrivelsen', '<strong><em>', 'allow_empty');
		$validator->isNumeric($array_var['unit'], 'Fejl i unit');

 		$validator->isNumeric($array_var['state_account_id'], 'Fejl i state_account', 'allow_empty');
		$validator->isNumeric($array_var['do_show'], 'Fejl i do_show', 'allow_empty');
		$validator->isNumeric($array_var['vat'], 'Fejl i vat');
		$validator->isNumeric($array_var['pic_id'], 'Fejl i billedid', 'allow_empty');
		$validator->isNumeric($array_var['weight'], 'Fejl i vægt - skal være et helt tal', 'allow_empty');

		$validator->isNumeric($array_var['price'], 'Fejl i pris', 'allow_empty');

		if ($this->product->error->isError()) {
			return 0;
		}

		return 1;
	}


	/**
	 * Public: Denne funktion gemmer data. At gemme data vil sige, at den gamle adresse gemmes, men den nye aktiveres.
	 *
	 * @param  (array) $array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
	 * @return (int)   Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme på en old_address.
	 */
	function save($array_var) {

		$array_var = safeToDb($array_var);

		$amount = new Amount($array_var['price']);
		$amount->convert2db();
		$array_var['price'] = $amount->get();


		if($this->old_detail_id != 0) {
			// save kan ikke bruges hvis man skal opdatere et gammelt produkt
			// men så bør den vel bare automatisk kalde update(), som i øjeblikket
			// er udkommenteret.
			return 0;
		}
		elseif (count($array_var) == 0) {
			// Der er ikke noget indhold i arrayet
			return 0;
		}

		$this->db->query("SELECT * FROM product_detail WHERE id = ".$this->detail_id . "
				AND product_id = " . $this->product->get('id'));

		if($this->db->nextRecord()) {
			// her skal vi sørge for at få billedet med
			$do_update = 0;
			for ($i=0, $max = sizeof($this->fields), $sql = ''; $i<$max; $i++) {
				if (!array_key_exists($this->fields[$i], $array_var)) {
					continue;
				}
				if(isset($array_var[$this->fields[$i]])) {
					$sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
				}
				else {
					$sql .= $this->fields[$i]." = '', ";
				}
				if(isset($array_var[$this->fields[$i]]) AND $this->db->f($this->fields[$i]) != $array_var[$this->fields[$i]] OR (is_numeric($this->db->f($this->fields[$i]) AND $this->db->f($this->fields[$i])) > 0)) {
					$do_update = 1;
				}
			}
			if ($this->db->f('pic_id') > 0) {
				$picture_id = $this->db->f('pic_id');
			}
		}
		else {
			// der er ikke nogen tidligere poster, så vi opdatere selvfølgelig
			$do_update = 1;
			for ($i=0, $max = sizeof($this->fields), $sql = ''; $i<$max; $i++) {
				if (!array_key_exists($this->fields[$i], $array_var)) {
					continue;
				}
				if(isset($array_var[$this->fields[$i]])) {
					$sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
				}
				else {
					$sql .= $this->fields[$i]." = '', ";
				}
			}
		}


		if($do_update == 0) {
			// Hmmmmm, der er slet ikke nogen felter der er ændret! Så gemmer vi ikke, men siger at det gik godt :-)

			return 1;
		}
		else {
			// vi opdaterer produktet
			$this->db->query("UPDATE product_detail SET active = 0 WHERE product_id = " . $this->product->get('id'));
			$this->db->query("INSERT INTO product_detail SET ".$sql." active = 1, changed_date = NOW(), product_id = " . $this->product->get('id') . ", intranet_id = " . $this->product->kernel->intranet->get('id'));
			$this->detail_id = $this->db->insertedId();
			$this->load();
			$this->old_detail_id = $this->detail_id;
			/*
			if (!empty($picture_id) AND $picture_id > 0) {
				$this->setPicture($picture_id);
			}
			*/
			return 1;
		}
	}
	/*
	function setPicture($pic_id) {
		$pic_id = (int)$pic_id;
		$db = new DB_Sql;
		$db->query("UPDATE product_detail SET pic_id = '".$pic_id."' WHERE id = " . $this->old_detail_id);
		return 1;
	}

	function deletePicture() {
		$pic_id = (int)$pic_id;
		$db = new DB_Sql;
		$db->query("UPDATE product_detail SET pic_id = 0 WHERE id = " . $this->old_detail_id);
		return 1;
	}
	*/


	/**
	 * Public: Opdatere produktdetaljer.
	 *
	 * Denne funktion overskriver de nuværende produktdetaljer. Benyt som udagangspunkt ikke denne, da historikken på produktdetaljer ikke gemmes så.
	 *
	 * @param	(array)$array_var	et array med felter med adressen. Se felterne i init funktionen: $this->fields
	 * $return	(int)	Returnere 1 hvis arrayet er gemt, 0 hvis det ikke er. Man kan ikke gemme på en old_address.
	 */
	function update($array_var) {
		die("ProductDetail->update(): Denne funktion skal vist ikke bruges til noget?");
		/*
		if($this->old_detail_id != 0) {
			return(0);
		}
		elseif($this->detail_id == 0) {
			$this->save($array_var);
		}
		else {
			$sql = "";
			for($i = 0; $i < count($this->fields); $i++) {
				if(isset($array_var[$this->fields[$i]])) {
					$sql .= $this->fields[$i]." = '".$array_var[$this->fields[$i]]."', ";
				}
				else {
					$sql .= $this->fields[$i]." = '', ";
				}
			}

			$this->db->query("UPDATE product_detail SET ".$sql." changed_date = NOW() WHERE id = ".$this->detail_id . " AND product_id = " . $this->product->get('id'));
			$this->load();
			return(1);
		}
    */
	}

}
?>
