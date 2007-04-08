<?php
/**
 * Denne klasse bruges til at flytte rundt på rækkefølgen på poster i en
 * database.
 *
 * Eksempel:
 * --------
 *
 * $position = new Position('cms_page', 'site_id='.$cmssite->get('id'), 'position', 'id');
 * $position->moveUp();
 *
 * @require	Klassen kræver databaseklassen DB_Sql.
 * @author		Sune Jensen <sj@sunet.dk>
 * @author		Lars Olesen <lars@legestue.net>
 *
 * CHANGELOG:
 * 2006-11-13	lo	Added function moveToMax().
 * 2006-10-14	lo	Added new parameters to Position::reposition, så repositioneringen kan
 *					starte fra et valgfrit sted.
 *				lo	Tilføjet $this->ekstrawhere i reposition i UPDATE i WHILE-løkken. Giver
 *					vist noget ekstra sikkerhed
 *				lo	Tilføjet ny funktion. moveTo();
 */

class Position {
	var $tabel;
	var $ekstrawhere;
	var $postionsfelt;
	var $idfelt;


	/**
	 * Konstruktør
	 *
	 * ALLE ændringer vil ske med en where sætning, der indholder position og id.
	 *
	 * Eksempel:
	 * --------
	 * $position_set = new position("indhold_site", "barn_af = 0 AND sprog = $session_sprog", "position", "id");
	 *
	 * @param	string	$tabel	Navnet på tabellen i databasen
	 * @param	string	$extrawhere	Bruges til at sætte ekstraparameter i SQL-sætning. Uden "AND" i starten af strengen.
	 * @param	string	$positionsfelt	Indeholder navnet der indeholder postens position, ofte position
	 * @param	integer	$idfelt	Unikt felt for tabellen. Ofte id.
	 */

	function Position($tabel = '', $ekstrawhere = '', $positionsfelt='position', $idfelt='id') {
		$this->tabel = $tabel;
		$this->ekstrawhere = $ekstrawhere;
		$this->positionsfelt = $positionsfelt;
		$this->idfelt = $idfelt;
	}

	/**
	 * Bruges til at ændre $ekstrawhere parameteren
	 *
	 * Eksempel:
	 * --------
	 * $position_set->new_where("barn_af = 1 AND sprog = $session_sprog");
	 *
	 * @param	string	$ekstrawhere	Ny where sætning. Uden "AND" i starten
	 */

	function new_where($ekstrawhere) {
		$this->ekstrawhere = $ekstrawhere;
	}

	/**
	 * Flytter en post en position op ad
	 *
	 * Eksempel:
	 * $position->moveUp(34);
	 *
	 * @param	integer	$id	id på posten (Det felt som er angivet i $idfelt i position()
	 * @return	integer	1 på succes / 0 på fiasko
	 */

	function moveUp($id) {

		$db = new DB_Sql;
		$db2 = new DB_Sql;

		$this->reposition();

		if($this->ekstrawhere != '') {
			$ekstrawhere = " AND ".$this->ekstrawhere;
		}
		else {
			$ekstrawhere = '';
		}

		// Finder position for post
		$sql = "SELECT $this->positionsfelt, $this->idfelt FROM $this->tabel WHERE $this->idfelt = " . $id . " $ekstrawhere LIMIT 1";
		$db->query($sql);

		if($db->nextRecord()) {
			if($db->f($this->positionsfelt) == 1) {
				//trigger_error("Denne post er den øverste og kan ikke flyttes op", E_USER_WARNING);
				return(0);
			}
			else {
				$sql = "SELECT " . $this->idfelt . " FROM " . $this->tabel . " WHERE " . $this->positionsfelt . " < " .$db->f($this->positionsfelt)." " . $ekstrawhere . " ORDER BY " . $this->positionsfelt . " DESC";
				$db2->query($sql);
				if($db2->nextRecord()) {
					$sql = "UPDATE " . $this->tabel . " SET " . $this->positionsfelt . "=" . $this->positionsfelt . "+1 WHERE " . $this->idfelt ."=".$db2->f($this->idfelt)." " . $ekstrawhere;
					$db2->query($sql);
					$sql = "UPDATE " . $this->tabel . " SET " . $this->positionsfelt ."=". $this->positionsfelt ."-1 WHERE " . $this->idfelt ."=".$db->f($this->idfelt)." " . $ekstrawhere;
					$db2->query($sql);
				  return(1);
				}
				else {
					//trigger_error("Kunne ikke flytte posten. Ingen post før", E_USER_WARNING);
					return(0);
				}
			}
		}
		else {
			//trigger_error("Kunne ikke flytte posten. Posten eksisterede ikke.", E_USER_WARNING);
			return(0);
		}
		$this->reposition();
		return 1;
	}

	/**
	 * Flytter posten 1 ned
	 *
	 * Eksempel:
	 * $position->moveDown(34);
	 *
	 * @param integer	$id	id på posten (Det felt som er angivet i $idfelt i position()
	 * @return	integer	Returnere 1 hvis success, 0 hvis fejl
	 */
	function moveDown($id) {

		$db = new DB_Sql;
		$db2 = new DB_Sql;

		$this->reposition();

		if($this->ekstrawhere != "") {
			$ekstrawhere = " AND ".$this->ekstrawhere;
		}

		// Finder position for post
		$sql = "SELECT $this->positionsfelt, $this->idfelt FROM $this->tabel WHERE $this->idfelt = $id $ekstrawhere LIMIT 1";
		$db->query($sql);
		if($db->nextRecord()) {
			if($db->f($this->positionsfelt) == $this->maxpos()) {
				//trigger_error("Denne er allerede nederst, så den kunne ikke flyttes ned", E_USER_WARNING);
				return(0);
			}
			else {
			  $sql = "SELECT $this->idfelt FROM $this->tabel WHERE $this->positionsfelt > ".$db->f($this->positionsfelt)." $ekstrawhere ORDER BY $this->positionsfelt";
				$db2->query($sql);
				if($db2->nextRecord()) {

				  $sql = "UPDATE $this->tabel SET $this->positionsfelt = $this->positionsfelt - 1 WHERE $this->idfelt = ".$db2->f($this->idfelt)." $ekstrawhere";
					$db2->query($sql);
					$sql = "UPDATE $this->tabel SET $this->positionsfelt = $this->positionsfelt + 1 WHERE $this->idfelt = ".$db->f($this->idfelt)." $ekstrawhere";
					$db2->query($sql);
					return(1);

				}
				else {
					//trigger_error("Kunne ikke flytte posten. Ingen post efter", E_USER_WARNING);
					return(0);
				}
			}
		}
		else {
			//trigger_error("Kunne ikke flytte posten. Posten eksisterede ikke.", E_USER_WARNING);
			return(0);
		}
		$this->reposition();
		return 1;
	}

	/**
	 * Bruges til at placere en post på en bestemt id.
	 * Mangler et eksempel på et godt interface.
	 *
	 * @param	integer	$id	Id på posten der skal flyttes
	 * @param	integer	$position	Den position id'en skal have
	 */

	function moveTo($id, $position) {
		// først lægger vi en til alle posterne fra det nummer denne post vil have
		$this->reposition($position, $position+1);
		$db = new DB_Sql;

		if ($this->ekstrawhere != '') {
			$ekstrawhere = " AND " . $this->ekstrawhere;
		}

		$sql = "UPDATE " . $this->tabel . " SET " . $this->positionsfelt . " = " . $position . " WHERE " . $this->idfelt . " = ".$id . $ekstrawhere;
		$db->query($sql);
		return 1;
	}

	/**
	 * Bruges til at placere en ny post på den sidste id.
	 *
	 * Eksempel:
	 * $position->moveToMax();
	 *
	 * @param $id integer Id på den post der skal flyttes til sidste post
	 */


	function moveToMax($id) {
		$db = new DB_Sql;

		if ($this->ekstrawhere != '') {
			$ekstrawhere = " AND " . $this->ekstrawhere;
		}

		$maxpos = $this->maxpos() + 1;

		$sql = "UPDATE " . $this->tabel . " SET " . $this->positionsfelt . " = " . $maxpos . " WHERE " . $this->idfelt . " = ".$id . $ekstrawhere;
		$db->query($sql);
		return 1;

	}



	/**
	 * Repositionere alle poster, så de får løbende positioner startende fra $position
	 *
	 * Eksempel:
	 * $position->reposition();
	 *
	 * @param	$start_from_position	Det tal repositioneringen skal starte fra. Optional.
	 * @param	$new_position		Den nye position posterne får
	 */

	function reposition($start_from_position = 0, $new_position = 1) {

		$db = new DB_Sql;
		$db2 = new DB_Sql;

		if($this->ekstrawhere != "") {
			$where = " WHERE $this->ekstrawhere";
			$ekstrawhere = " AND " . $this->ekstrawhere; // bruges i db2
		}
		else {
			$where = 'WHERE 1=1';
			$ekstrawhere = '';
		}

		$sql = "SELECT $this->positionsfelt, $this->idfelt FROM $this->tabel $where AND $this->positionsfelt >= $start_from_position ORDER BY $this->positionsfelt";
		$db->query($sql);
		while($db->nextRecord()) {
			$sql = "UPDATE ".$this->tabel." SET ".$this->positionsfelt." = ".$new_position." WHERE ".$this->idfelt." = ".$db->f($this->idfelt) . " " . $ekstrawhere;
			$db2->query($sql);
			$new_position++;
		}
	}


	/**
	 * Finder den højeste position
	 *
	 * Eksempel:
	 * $position->maxpos();
	 *
	 * @return	integer	Returnere tal med den højeste position
	 */

	function maxpos() {
		$db = new DB_Sql;

		if($this->ekstrawhere != "") {
			$where = " WHERE $this->ekstrawhere";
		}
		$sql = "SELECT MAX(".$this->positionsfelt.") AS maxpos FROM ".$this->tabel." ".$where;
		$db->query($sql);
		if($db->nextRecord()) {
			return $db->f("maxpos");
		}

		return 0;
	}
}

?>