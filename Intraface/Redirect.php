<?php
/**
 * Klasse til at sørge for at en bruger bliver sendt de rigtige steder hen i
 * systemet.
 *
 * Brug:
 * ----
 *
 * På den side som brugeren starter sin redirect (Det er ikke nødvendigvis den side
 * man kommer tilbage til, men den side, hvor man ved hvilken side brugeren skal til
 * efter næste side.)
 *
 * $redirect = Redirect::factory($kernel, 'go' [, 'et_andet_querystring_navn' [, 'et_andet_return_querystring_navn']]);
 * et_andet_querystring_navn er den variabel som sendes i url'en med id på redirect.
 * Den skal være ens på sender og modtager siderne. Default er 'redirect_id'
 * $url = $redirect->setDestination($module->getPath().'edit_product.php' [, $accounting_module->getPath().'state.php?id='. $debtor->get('id')]); $destination_url [, $return_url]
 * evt. $redirect->askParameter('add_contact_id' [, 'multiple']); // Denne aktivere at parameteren bliver sendt med tilbage til return siden. Med 'multiple', så kan der kommer der et array med flere værdier tilbage
 * evt. $redirect->setIdentifier('sted_1'); Identifier kan sættes, hvis der er flere redirects på samme side, så kan man finde frem til når man kommer tilbage, hvorfra man kom.
 * header("location: ".$url);
 *
 *
 * På den side brugeren bliver sendt hen og bagefter skal sendes tilbage til foregående/næste side.
 *
 * $redirect = Redirect::factory($kernel, 'receive' [, 'et_andet_querystring_navn' [, 'et_andet_querystring_return_navn']] ); // Skal kaldes på alle visninger på siden.
 *
 * if(isset($_POST['submit'])) {
 *   // Gem eller noget andet
 *   evt. $redirect->setParameter("add_contact_id", $added_id); // Denne sætter parameter som skal sendes tilbage til siden. Den sendes dog kun tilbage hvis askParameter er sat ved opstart af redirect. Hvis ask er sat til multiple, så gemmes der en ny hver gang den aktiveres, hvis ikke, overskrives den
 *   header('Location: '.$redirect->getRedirect('standard.php')); // 'standard.php' er den side man bliver sendt til, hvis der ikke er en redirect.
 * }
 *
 * <a href="<?php print($redirect->getRedirect('standard.php')); ?>">Fortryd</a>
 *
 * Har man behov for at lave en videre redirect, (cms -> select_file.php -> upload.pgp) gøres det således:
 * if($go_further) {
 * 	$new_redireict = Redirect::factory($kernel, 'go');
 * 	$url = $new_redirect->setDestination($module->getPath().'edit.php', $module->getPath().'select_file.php?'.$redirect->get('redirect_query_string'));
 * 	header('Location: '.$url);
 * }
 * BEMÆRK at redirect_query_string (indeholder: redirect_id=[id]) på denne sides redirect skal sættes. -----^
 *
 *
 *
 * På siden man ender på (er oftest den samme som man kommer fra i første omgang), hvis man skal benytte parameter
 * if(isset($_GET['return_redirect_id'])) {
 * 	$redirect = Redirect($kernel, 'return');
 * 	evt $redirect->get('identifier'); returnere den identifier, som blev sat i starten
 * 	$selected_values = $redirect->getParameter('add_contact_id'); returnere array hvis ask var 'multiple' ellers værdi.
 * 	evt $redirect->delete(); deletes the redirect, so that the action is not done again on the use of Back button.
 * }
 *
 *
 * BEMÆRK:
 * Systemet til automatisk at hente redirect_id og return_redirect_id er basseret på GET variabler. Har du behov for POST,
 * ja, så skriv til sune, så implementerer han det. I første omgang kan du benytte $redirect = new Redirect($kernel, $_POST['redirect_id|return_redirect_id']); $redirect->reset();
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Standard.php';

class Redirect extends Standard {

	var $kernel;
	var $value;
	var $querystring = array();
	var $identifier;

	function __construct($kernel, $id = 0) { // , $query_variable = "redirect_id", $query_return_variable = 'return_redirect_id'
		$this->kernel = $kernel;

		$this->value['query_variable'] = 'redirect_id';
		$this->value['query_return_variable'] = 'return_redirect_id';

		$this->id = (int)$id;
		if($this->id > 0) {
			$this->load();
		}

		/*
		if(intval($id) != 0) {
			$this->id = intval($id);
			$this->load();
			// Her sletter vi ikke andre redirects til denne side, da der kan være nogle som referer til denne side.
		}
		elseif(isset($_GET[$this->get('query_variable')])) {
			// Er id'et sat i url'en, så loader vi fra id'et
			$this->id = intval($_GET[$this->get('query_variable')]);
			$this->load();

			// Sletter alle andre redirects til denne side.
			$this->reset();
		}
		elseif(isset($_GET[$this->get('query_return_variable')])) {
			// Er id'et sat i url'en, så loader vi fra id'et
			$this->id = intval($_GET[$this->get('query_return_variable')]);
			$this->load();
		}
		elseif(isset($_SERVER['HTTP_REFERER'])) {
			$url_parts = explode("?", $_SERVER['HTTP_REFERER']);
			// print($url_parts[0]." == ".$_SERVER["SCRIPT_URI"]);
			// print("b");

			if($url_parts[0] == $_SERVER["SCRIPT_URI"]) {
				// print("c");
				// Vi arbejder inden for den samme side, så finder vi id ud fra siden.
				$db = new DB_sql;
				//print "SELECT id FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND user_id = ".$this->kernel->user->get('id')." AND destination_url = \"".$_SERVER["SCRIPT_URI"]."\"";
				$db->query("SELECT id FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND user_id = ".$this->kernel->user->get('id')." AND destination_url = \"".$_SERVER["SCRIPT_URI"]."\" ORDER BY date_created DESC");
				if($db->nextRecord()) {
					$this->id = $db->f('id');
					$this->load();
					// Sletter alle andre redirects til denne side.
					$this->reset();
				}
				else {
					$this->id = 0;
				}
			}
			else {
				// print("d");
				// Der er ikke sat et redirect_id, vi er ikke inden for samme side, så må det være et kald til siden som ikke benytter redirect. Vi sletter alle redirects til denne side.

				$this->id = 0;

				//
				// DET KAN VI IKKE BARE. TJEK FX FRA REMINDER_EMAIL.PHP, hvor vi så ikke vil blive
				// sendt tilbage, men det kan være, at det er fordi den bruges forkert?
				// Det må være forkert brug, for vi er nødt til at ryde op her, ellers giver det problemer /Sune (17-10-2006)
				//

				$this->reset();
			}
		}
		*/

	}

	function factory($kernel, $type, $query_variable = "redirect_id", $query_return_variable = 'return_redirect_id') {

		if(!is_object($kernel) || strtolower(get_class($kernel)) != "kernel") {
			trigger_error("Førse parameter i redirect::factory er ikke kernel", E_USER_ERROR);
		}

		if(!in_array($type, array('go', 'receive', 'return'))) {
			trigger_error("Anden parameter i Redirect->factory er ikke enten 'go', 'receive' eller 'return'", E_USER_ERROR);
		}

		$reset = false;
		$id = 0;
		if($type == 'go') {
			// Vi starter en ny redirect på siden, derfor skal vi ikke her slette eksisterende redirects til denne side.
			$id = 0;
		}
		else {
			if(($type == 'receive' && isset($_GET[$query_variable]))) {
				// Vi modtager en redirect fra url'en. Derfor sletter vi alle andre redirects til denne side.
				$reset = true;
				$id = intval($_GET[$query_variable]);
				$redirect = new Redirect($kernel, $id);

			}
			elseif($type == 'return' && isset($_GET[$query_return_variable])) {
				// Vi returnerer med en værdi. Der kan være en eksisterende redirect til denne side, som vi skal benytte igen. Vi sletter ikke andre redirects.
				$id = intval($_GET[$query_return_variable]);
			}
			elseif(isset($_SERVER['HTTP_REFERER'])) {
				// Vi arbejder inden for samme side. Vi finder forhåbentligt en redirect. Under alle omstændigheder sletter vi hvad vi ikke skal bruge.
				$reset = true;

				$url_parts = explode("?", $_SERVER['HTTP_REFERER']);
				// print("b");

  				$this_uri = Redirect::thisUri();

				// print($this_uri.' == '.$url_parts[0]);
				if($this_uri == $url_parts[0]) {
					// print("c");
					// Vi arbejder inden for den samme side, så finder vi id ud fra siden.
					$db = new DB_sql;
					//print "SELECT id FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND user_id = ".$this->kernel->user->get('id')." AND destination_url = \"".$_SERVER["SCRIPT_URI"]."\"";
					$db->query("SELECT id FROM redirect WHERE intranet_id = ".$kernel->intranet->get('id')." AND user_id = ".$kernel->user->get('id')." AND destination_url = \"".$this_uri."\" ORDER BY date_created DESC");
					if($db->nextRecord()) {

						$id = $db->f('id');
					}
					else {

						$id = 0;
					}
				}
				else {
					// print("d");
					// Der er ikke sat et redirect_id, vi er ikke inden for samme side, så må det være et kald til siden som ikke benytter redirect. Vi sletter alle redirects til denne side.
					$reset = true;
					$id = 0;
				}
			}


		}

		$redirect = new Redirect($kernel, $id);
		if($reset) {
			$redirect->reset();
		}
		$redirect->set('query_variable', $query_variable);
		$redirect->set('query_return_variable', $query_return_variable);

		return $redirect;
	}

	function set($key, $value) {
		if($key != '') {
			$this->value[$key] = $value;
		}
		else {
			trigger_error("Key er ikke sat i Redirect->set", E_USER_ERROR);
		}
	}

	function load() {

		$db = new DB_Sql;
		$sql = "SELECT * FROM redirect
			WHERE intranet_id = ".$this->kernel->intranet->get('id')."
			AND user_id = ".$this->kernel->user->get('id')."
			AND id = ".$this->id;
		$db->query($sql);
		if(!$db->nextRecord()) {
			$this->id = 0;
			$this->value['id'] = 0;
			return 0;
		}

		$this->value['id'] = $db->f('id');
		$this->value['from_url'] = $db->f('from_url');
		$this->value['return_url'] = $db->f('return_url');
		$this->value['destination_url'] = $db->f('destination_url');
		$this->value['identifier'] = $db->f('identifier');


		$this->value['redirect_query_string'] = $this->get('query_variable')."=".$this->id;

		return $this->id;
	}


	/**
	 * Skal parse url'en og give en fejlmeddelelse, hvis det ikke er en gyldig url.
	 *
	 * @param $url som skal parses
	 */

	function parseUrl($url) {
		return $url;
	}

	function setIdentifier($identifier) {
		if($this->id) {
			$db = new DB_sql;
			$db->query("UPDATE redirect SET identifier = \"".safeToDb($identifier)."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
		}
		else {
			$this->identifier = safeToDB($identifier);
		}
	}

	/*
	 * Return uri to current file
	 */
	function thisUri() {
		$protocol = 'http://';
  		if(!empty($_SERVER['HTTPS'])) { $protocol= 'https://'; }
  		return $protocol.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
	}


	/**
	 * $url destionationsurl. Den url redirect skal virke fra
	 * @param $this_url Url der skal sendes tilbage til.
	 *
	 * @return den url der skal benyttes til redirect.
	 */
	function setDestination($destination_url, $return_url = '') {
		if (!array_key_exists('SCRIPT_URI', $_SERVER)) {
			$_SERVER['SCRIPT_URI'] = $_SERVER['REQUEST_URI'];
		}

		if(empty($return_url)) {

			$return_url = $this->parseUrl($this->thisUri());
		}
		else {
			$return_url = $this->parseUrl($return_url);
		}



		$destination_url = $this->parseUrl($destination_url);


		if(substr($destination_url, 0, 7) != 'http://' && substr($destination_url, 0, 8) != 'https://') {
			trigger_error("Første parameter i Redirect->setDestination skal være den komplette sti", E_USER_ERROR);
		}

		if(substr($return_url, 0, 7) != 'http://' && substr($return_url, 0, 8) != 'https://') {
			trigger_error("Anden parameter i Redirect->setDestination skal være den komplette sti", E_USER_ERROR);
		}


		// Det er kun den rene url der skal gemmes uden query strings, så den senere kan sammenlignes med $_SERVER['SCRIPT_URI']
		$url_parts = explode("?", $destination_url);

		$db = new DB_Sql;
		$db->query("INSERT INTO redirect
			SET
				from_url = \"".$_SERVER['SCRIPT_URI']."\",
				return_url = \"".$return_url."\",
				destination_url = \"".$url_parts[0]."\",
				intranet_id = ".$this->kernel->intranet->get('id').",
				user_id = ".$this->kernel->user->get('id').",
				identifier = \"".$this->identifier."\",
				date_created = NOW()");
		$this->id = $db->insertedId();
		$this->load();

		$destination_url = $this->mergeQueryString($destination_url, $this->get('redirect_query_string'));

		// $this->reset($this->url_destination); // vi sletter tidligere redirects til denne side.
		//$this->reset(); // sletter alle tidligere redirects for brugen er vist det rigtige

		return $destination_url;
	}


	/**
	 * Kun redirect hvis nuværende url svarer til url_destination i databasen. Formentlig
	 * problematisk i længden at bruge getThisUrl
	 */

	function getRedirect($standard_location) {

		if($this->id > 0) {
			$this->addQuerystring($this->get('query_return_variable').'='.$this->id);
			return $this->mergeQuerystring($this->get('return_url'), $this->querystring);
		}
		else {
			return $standard_location;
		}
	}


	/**
	 * Tilføj querystrings til return_url
	 *
	 */
	function addQueryString($querystring) {
		if(in_array($querystring, $this->querystring) === false) {
			// Hvis den samme querystring allerede er sat, så sættes den ikke igen.
			$this->querystring[] = $querystring;
		}
	}


	/**
	 * Samler extra parameter på en eksisterende querystring med det rigtig & eller ?.
	 *
	 *@param extra: kan både være en streng eller et array med parameter der skal sættes på
	 */
	function mergeQueryString($querystring, $extra) {

		if(strstr($querystring, "?") === false) {
			$separator = "?";
		}
		else {
			$separator = '&';
		}

		if(is_array($extra) && count($extra) > 0) {
			return $querystring.$separator.implode('&', $extra);
		}
		elseif(is_string($extra) && $extra != "") {
			return $querystring.$separator.$extra;
		}
		else {
			return $querystring;
		}

	}

	/**
	 * Vi sletter redirects der er mere end 24 timer gamle
	 *
	 */

	function reset() {
		/*
		if (!array_key_exists('SCRIPT_URI', $_SERVER)) {
			$_SERVER['SCRIPT_URI'] = $_SERVER['REQUEST_URI'];
		}
		*/

		if($this->id == 0) {
			// Kan de nu også være rigtigt at den ikke kan slette hvor id er 0!
			// trigger_error("id er ikke sat i Redirect->reset", E_USER_ERROR);
		}

		$db = new DB_Sql;

		// Vi sletter de
		$db->query("SELECT id FROM redirect
			WHERE
				(intranet_id = ".$this->kernel->intranet->get('id')."
					AND user_id = ".$this->kernel->user->get('id')."
					AND id != ".$this->id."
					AND destination_url = \"".$this->thisUri()."\")
				OR (intranet_id = ".$this->kernel->intranet->get('id')."
					AND date_created < DATE_SUB(NOW(), INTERVAL 24 HOUR))");

		while($db->nextRecord()) {
			$this->delete($db->f('id'));
		}

		return true;
	}

	/*
	 * Delete a single redirect.
	 *@param id: id of redirect or if not set the current redirect.
	 *@return bol true on success
	 */

	function delete($id = NULL) {
		if($id === NULL) {
			$id = $this->id;
		}
		if($id == 0) {
			return true;
		}
		$db = new DB_Sql;
		$db->query("DELETE FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".intval($id));
		$db->query("DELETE FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".intval($id));
		$db->query("DELETE FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".intval($id));
		return true;
	}



	/**
	 * Benyttes til at tilføje en parameter, som skal sendes tilbage til this_url
	 *
	 * Funktionen kaldes umiddelbart efter setDestination
	 **/
	/*
	function askParameter($key) {
		$key = safeToDb($key);
		if($this->id == 0) {
			trigger_error("Der skal gemmes en redirect med setDestination før der kan sættes en askParameter", FATAL);
			return false;
		}

		$db = new DB_Sql;
		$db->query("INSERT INTO redirect_parameter SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", parameter = \"".$key."\"");
		return true;
	}
	*/

	/**
	 * Benyttes til at sætte efterspurgte parameter, hvis der skal være mulighed for at gemme flere.
	 * @param $return_trigger er den variable der i uri vil fortælle at man kommer tilbage med multiple parameter. Den vil indeholde id på redirect.
	 */
	function askParameter($key, $type = 'single') {
		$key = safeToDb($key);
		$type = safeToDb($type);
		if($this->id == 0) {
			trigger_error("Der skal gemmes en redirect med setDestination før der kan sættes en askParameter", E_USER_EROR);
			return false;
		}

		$multiple = 0;
		if(!in_array($type, array('single', 'multiple'))) trigger_error('Ugyldig type "'.$type.'" i Redirect->askParameter. Den kan være "single" eller "multiple"', E_USER_ERROR);
		if($type == 'multiple') $multiple = 1;

		$db = new DB_Sql;
		$db->query("INSERT INTO redirect_parameter SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", parameter = \"".$key."\", multiple = \"".$multiple."\"");
		return true;
	}

	/**
	 * Benyttes til at sætte efterspurgte parameter. Både enkel og multiple
	 * Kaldes umiddelbart før location
	 **/
	function setParameter($key, $value, $extra_value = '') {
		if($this->id == 0) {
			trigger_error("id is not set IN Redirect->setParameter. You might want to consider the possibility that redirect id both could and could not be set by the call of setParameter, and therefor want to make a check before.", E_USER_ERROR);
		}

		$key = safeToDb($key);
		$value = safeToDb($value);
		$extra_value = safeToDb($extra_value);

		$db = new DB_sql;
		$db->query("SELECT id, multiple FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\"");
		if($db->nextRecord()) {
			$parameter_id = $db->f('id');

			if($db->f('multiple') == 1) {
				$db->query("INSERT INTO redirect_parameter_value SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", redirect_parameter_id = ".$db->f('id').", value = \"".$value."\", extra_value = \"".$extra_value."\"");
				return true;
			}
			else {

				$db->query("SELECT id FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND redirect_parameter_id = ".$db->f('id'));
				if($db->nextRecord()) {
					$db->query("UPDATE redirect_parameter_value SET value = \"".$value."\", extra_value = \"".$extra_value."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND  redirect_parameter_id = ".$parameter_id);
				}
				else {
					$db->query("INSERT INTO redirect_parameter_value SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", redirect_parameter_id = ".$parameter_id.", value = \"".$value."\", extra_value = \"".$extra_value."\"");
				}
				return true;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Benyttes til at kunne kontrollere om det er en multiple parameter
	 *
	 */
	function isMultipleParameter($key) {
		if($this->id == 0) {
			trigger_error("id er ikke sat i Redirect->isMultipleParameter", E_USER_ERROR);
		}
		$key = safeToDb($key);
		$db = new DB_sql;
		$db->query("SELECT id FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\" AND multiple = 1");
		return $db->nextRecord();
	}


	function removeParameter($key, $value) {
		if($this->id == 0) {
			trigger_error("id er ikke sat i Redirect->removeParameter", E_USER_ERROR);
		}

		$key = safeToDb($key);
		$value = safeToDb($value);

		$db = new DB_sql;
		$db->query("SELECT id FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\"");
		if($db->nextRecord()) {
			$db->query("DELETE FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND redirect_parameter_id = \"".$db->f('id')."\" AND value = \"".$value."\"");
			return true;
		}
		return false;
	}

	/**
	 * Benyttes til at hente multiple paramenter
	 */
	function getParameter($key, $with_extra_value = '') {
		if($this->id == 0) {
			trigger_error("id er ikke sat i Redirect->getMultipleParameter", E_USER_ERROR);
		}

		$key = safeToDb($key);
		$db = new DB_sql;
		$i = 0;
		$parameter = array();
		$multiple = 0;
		$db->query("SELECT id, multiple FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\"");
		if($db->nextRecord()) {
			$multiple = $db->f('multiple');
			$db->query("SELECT id, value, extra_value FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_parameter_id = ".$db->f('id'));
			while($db->nextRecord()) {
				if($with_extra_value == 'with_extra_value') {

					$parameter[$i]['value'] = $db->f('value');
					$parameter[$i]['extra_value'] = $db->f('extra_value');
				}
				else {
					$parameter[$i] = $db->f('value');
				}
				$i++;
			}
		}


		if($multiple == 1) {
			return $parameter;
		}
		else {
			if (array_key_exists(0, $parameter)) {
				return $parameter[0];
			}
			else {
				return '';
			}
		}
	}
}

?>