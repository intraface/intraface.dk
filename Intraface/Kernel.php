<?php
/**
 * Kernel - a registry
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'Weblogin.php';

class Kernel {
	private $db;
	public $intranet;
	public $user;
	public $primary_module_name;
	//public $session;
	private $_session;
	public $session_id;
	public $modules = array();
	public $shared;
	public $translation;
	private $observers = array();

	//static private $instance;

	/**
	 * Init
	 */
	function __construct($session = null) {
		$this->_session = $session;
		$this->db = MDB2:: singleton(DB_DSN);
		if (PEAR::isError($this->db)) {
			trigger_error($this->db->getMessage() . $this->db->getUserInfo(), E_USER_ERROR);
		}
	}

	/**
	 * Denne metode sørger bare for at autorisere brugeren. Hvis
	 * man kan autoriseres så skal isLoggedIn kaldes, ellers er man
	 * ikke logget ind i systemet.
	 * @param string $email	   user e-mail
	 * @param string $password user password
	 */
	/*
	public function login($email, $password) {
		$this->_session->set('session_id', md5(session_id()));

		$result = $this->db->query("SELECT id FROM user WHERE session_id = ".$this->db->quote($this->_session->get('session_id'), 'text'));
		if(PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
			return false;
		}

		if($result->numRows() > 0) {
			return LOGIN_ERROR_ALREADY_LOGGED_IN;
		}

		$result = $this->db->query("SELECT id FROM user WHERE email = ".$this->db->quote($email, 'text')." AND password = ".$this->db->quote(md5($password), 'text'));
		if(PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
			return false;
		}

		if($result->numRows() != 1) {
			$this->notify('login', $email .' tried to login - but failed');
			return LOGIN_ERROR_WRONG_CREDENTIALS;
		}
		$row = $result->fetchRow();

		$result = $this->db->exec("UPDATE user SET lastlogin = NOW(), session_id = ".$this->db->quote($this->_session->get('session_id'), 'text')." WHERE id = ".$this->db->quote($row['id'], 'integer'));
		if(PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
			return false;
		}

		$this->notify('login', $email .' logged in');

		return true;
	}
	*/

	function createUser($id) {
		return new User($id);
	}

	function createIntranet($id) {
		return new Intranet($id);
	}

	function createSetting($intranet_id, $user_id = 0) {
		return new Setting($intranet_id, $user_id);

	}

	function createWeblogin($session_id) {
		return new Weblogin($session_id);
	}

	/**
	 * Det er denne metode, der sørger for at man reelt er logget
	 * ind i systemet
	 */
	/*
	public function isLoggedIn() {

		$this->_session->set('session_id', md5(session_id()));

		$result = $this->db->query("SELECT id FROM user WHERE session_id = ". $this->db->quote($this->_session->get('session_id'), 'text'));

		if(PEAR::isError($result)) {

			print_r($result);
			trigger_error($result->getMessage() . $result->getUserInfo(), E_USER_ERROR);
			return false;
		}

		if($result->numRows() != 1) {
			return false;
		}

		$row = $result->fetchRow();
		$user_id = $row['id'];

		if($user_id != 0) {
			//$this->user = new User($user_id);  // kræver intranet->get('id') varum?
			$this->user = $this->createUser($user_id);
			//$this->intranet = new Intranet($this->getActiveIntranetId()); // kræver active_intranet_id kræver user->hasModuleAccess
			if (!$intranet_id = $this->getActiveIntranetId()) {
				return false;
			}
			$this->intranet = $this->createIntranet($intranet_id); // kræver active_intranet_id kræver user->hasModuleAccess
			$this->user->setIntranetId($this->intranet->get('id'));


			//$this->setting = new Setting($this->intranet->get('id'), $this->user->get('id'));
			$this->setting = $this->createSetting($this->intranet->get('id'), $this->user->get('id'));
			return true;
		}
		else {
			return false;
		}
	}
	*/

	/**
	 * Kan benyttes uden for intranettet, for at tjekke om den bruger der ser siden har en
	 *
	 * Bruges bl.a. i forbindelse med at hente billeder
	 */
	/*
	function remoteCheckLogin() {
		trigger_error('Kernel::remoteCheckLogin should not be used');
	}
	*/
	/**
	 *
	 *
	 * 	// type:
	 *	//   private: xml-rpc
	 *	//   public: f.eks. et billede
	 *
	 */

	function weblogin($type, $key, $session_id) {
		require_once 'Weblogin.php';

		if($type == 'private') {

			$result = $this->db->query("SELECT id FROM intranet WHERE private_key = " . $this->db->quote($key, 'text'));
			if(PEAR::isError($result)) {
				trigger_error($result->getUserInfo(), E_USER_ERROR);
			}
			if($result->numRows() == 0) {
				return ($intranet_id = false);
			}
			$row = $result->fetchRow();
			$intranet_id = $row['id'];

		}
		elseif($type == 'public') {

			$result = $this->db->query("SELECT id FROM intranet WHERE public_key = '".$key."'");
			if(PEAR::isError($result)) {
				trigger_error($result->getUserInfo(), E_USER_ERROR);
			}
			if($result->numRows() == 0) {
				return ($intranet_id = false);
			}
			$row = $result->fetchRow();
			$intranet_id = $row['id'];

		}
		else {
			trigger_error('Ugyldig type weblogin', E_USER_ERROR);
		}

		if($intranet_id === false) {
			return false;
		}
		else {
			$this->intranet = $this->createIntranet($intranet_id);
			$this->setting = $this->createSetting($this->intranet->get('id'));
		}
		$this->weblogin = $this->createWeblogin($session_id);

		return true;

	}

	/**
	 * Public: Går til loginside med besked - egentlig er den vel private?
	 *
	 * Denne funktion bruges hvis der er fejl i login. Normal vis skal $user->error() bruges istedet.
	 *
	 * @param (string)$msg	besked der bliver vist til brugeren
	 */
	 /*
	static public function toLogin($msg = '') {
		if(empty($msg)) {
			header('Location: '.PATH_WWW.'main/login.php');
			exit;
		}
		else {
			header('Location: '.PATH_WWW.'main/login.php?msg='.urlencode($msg));
			exit;
		}
	}
	*/

	/**
	 * Private: log ud
	 *
	 * Denne metode kan i øjeblikket kun kaldes, hvis der er en
	 * bruger ledig.
	 *
	 */

	/*
	function logout() {

		$this->db = & MDB2::singleton(DB_DSN);
		// spørgmsålet er om det ikke er nok bare at sige hvor session_id == session_id - ville sikkert gøre tests lettere
		//echo "UPDATE user SET session_id = '' WHERE id = ".$this->user->get('id');
		$result = $this->db->exec("UPDATE user SET session_id = '' WHERE id = ".$this->db->quote($this->user->get('id'), 'integer'));

		if (PEAR::isError($result)) {
			trigger_error($result->getMessage(), E_USER_ERROR);
		}

		$this->_session->destroy();

		$this->notify('logout', $this->user->get('id') . ' logged out');

		//$this->toLogin('Du er logget ud');
		return 1;
	}
	*/

	/**
	 * TODO Most of the functionality has been moved to user -
	 * entire method should be moved. - really it doesn't matter
	 * with the error msg - just that you cannot login
	 *
	 * Hvad bruges user_id til
	 *
	 * @param  int $user_id
	 * @return int intranet id
	 */
	/*
	function getActiveIntranetId($user_id = 0) {

		if (!$id = $this->user->getActiveIntranetId($user_id)) {
			trigger_error($user_id . ' is not added to any intranets', E_USER_NOTICE);
			//$this->toLogin('Du er ikke tilknyttet nogle intranet!');
			return false;
		}

		return $id;

	}
	*/

	/*
	 * TODO remove this function as the functionality has
	 * been moved to User.
	 */
	/*
	function setActiveIntranetId($id) {
		return $this->user->setActiveIntranetId($id);
	}
	*/

	/**
	 * Public: Sætter det primære modul for en side.
	 *
	 * @param	(string)$module_name	Navn på det modul der er primær modul
	 * @return	(object)	returnere modullets main object
	 */
	function module($module_name) {
		if(!empty($this->primary_module_object) AND is_object($this->primary_module_object)) {
			trigger_error('Det primære modul er allerede sat', E_USER_ERROR);
		}
		else {

			$module = $this->useModule($module_name);

			if(is_object($module)) {
				$this->primary_module_name = $module_name;

				// Finder afhængige moduller - Dette kunne flyttes til useModule, hvorfor er den egentlig ikke det? /Sune 06-07-2006
				$dependent_modules = $module->getDependentModules();

				for($i = 0, $max = count($dependent_modules); $i < $max; $i++) {
					$no_use = $this->useModule($dependent_modules[$i]);
				}


				return($module);
			}
			else {
				// Den fejlmeddelse er egentlig irrelevant, da useModul ikke enten returnere et objekt eller trigger_error.
				trigger_error('Du har ikke adgang til modulet', E_USER_ERROR);
				return false;
			}
		}
	}

	/**
	 * Er denne ikke overflødig. Kan man ikke bare bruge useModule? Man ved jo altid
	 * hvilket modul, man skal have fat i? 19. oktober 2006 LO
	 * It is used in Page.php, there we do not know otherwise what is primary module. /SJ 13/1 2007
	 */

	function getPrimaryModule() {
		if(!empty($this->modules[$this->primary_module_name]) AND is_object($this->modules[$this->primary_module_name])) {
			return($this->modules[$this->primary_module_name]);
		}
		else {
			return(0);
		}
	}

	function getModule($name) {
		if(is_object($this->modules[$name])) {
			return($this->modules[$name]);
		}
		else {
			trigger_error('Ugyldigt modulnavn eller modulet er ikke loadet i funktionen getModule: '.$name, E_USER_ERROR);
		}
	}

	/**
	 * Bruges bl.a. på forsiden og under administration
	 */

	function getModules($order_by = 'frontpage_index') {


		$modules = array();

		if($order_by != '') {
			$order_by = "ORDER BY ".safeToDB($order_by); // $this->db->quote can not be used here, while the text is not to be quoted
		}

		$i = 0;
		$result = $this->db->query("SELECT id, menu_label, name, show_menu FROM module WHERE active = 1 ".$order_by);
		if(PEAR::isError($result)) {
			trigger_error($result->getUserInfo(), E_USER_ERROR);
		}
		while($row = $result->fetchRow()) {
			// $module[$i] = $row;

			$modules[$i]['id'] = $row['id'];
			$modules[$i]['name'] = $row['name'];
			$modules[$i]['menu_label'] = $row['menu_label'];
			$modules[$i]['show_menu'] = $row['show_menu'];


			$j = 0;

			if (!isset($sub_modules)) {
				$sub_modules = array();

				//$result_sub = $db->query("SELECT id, description, module_id FROM module_sub_access WHERE active = 1 AND module_id = ".$db->quote($row["id"], 'integer')." ORDER BY description");
				$result_sub = $this->db->query("SELECT id, description, module_id FROM module_sub_access WHERE active = 1 ORDER BY description");
				if(PEAR::isError($result_sub)) {
					trigger_error($result_sub->getUserInfo(), E_USER_ERROR);
				}
				// $modules[$i]['sub_access'] = $result_sub->fetchAll();

				while($row_sub = $result_sub->fetchRow()) {
					$sub_modules[$row_sub['module_id']][$row_sub['id']]['id'] = $row_sub['id'];
					$sub_modules[$row_sub['module_id']][$row_sub['id']]['description'] = $row_sub['description'];
				}
			}
			// $row['id'] er module_id
			if (!empty($sub_modules[$row['id']]) AND count($sub_modules[$row['id']]) > 0) {
				foreach($sub_modules[$row['id']] AS $sub_module) {
					$modules[$i]['sub_access'][$j]['id'] = $sub_module['id'];
					$modules[$i]['sub_access'][$j]['description'] = $sub_module['description'];
					$j++;
				}
			}

			$i++;
		}
		return $modules;

	}

	/**
	 * Public: Giv adgang til et andet modul
	 *
	 * @param	(string)$module_name	Navn på det modullet der skal loades
	 * @param (bol) $ignore_user_access  Ved true, tjekker den ikke om brugeren har adgang, men kun om intranettet har. Benyttes bla. til når der skal trækkes vare fra lageret fra gennem faktura.
	 * @return	(object) || (0) Hvis man har adgang returnere den et object, ellers returnere den 0;
	 */
	function useModule($module_name, $ignore_user_access = false) {
		if(!ereg("^[a-z0-9]+$", $module_name)) {
			trigger_error('kernel says invalid module name '.$module_name, E_USER_ERROR);
			return false;
		}


		// Tjekker om modullet allerede er loaded
		if(!empty($this->modules[$module_name]) AND is_object($this->modules[$module_name])) {
			return $this->modules[$module_name];
		}

		$access = false;

		if(!is_object($this->user)) {
			// Det er et weblogin.
			if($this->intranet->hasModuleAccess($module_name)) {
				$access = true;
			}
		}
		elseif($ignore_user_access) {
			// Skal kun kontrollere om intranettet har adgang, for at benytte modullet
			if($this->intranet->hasModuleAccess($module_name)) {
				$access = true;
			}
		}
		else {
			// Almindelig login
			if($this->user->hasModuleAccess($module_name)) {
				$access = true;
			}
		}

		if($access == true) {
			$main_class_name = "Main".ucfirst($module_name);
			$main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";

			if(file_exists($main_class_path)) {
				require_once($main_class_path);
				$object = new $main_class_name;
				$object->load($this);
				$this->modules[$module_name] = $object;

				return $object;
			}
			else {
				trigger_error($main_class_path.' do not exist', E_USER_ERROR);
			}
		}
		else {
			trigger_error('Du mangler adgang til et modul for at kunne se denne side: '.$module_name, E_USER_ERROR);
			// Det her kan jeg ikke lige finde ud af, om den skal returnere nul eller den skal returnere fejl!
			// Det fungere fint når den returnere fejl. Hvis det laves om, skal der i hvertfald rettes i /debtor/debtorFactory.php /Sune (21/3 2005)
			// return(0);
		}

	}


	/**
	 * Public: Giv adgang til et shared
	 *
	 * @param	(string)$shared_name	Navn på det shared der skal loades
	 * @return	(object) || (0) Hvis man har adgang returnere den et object, ellers returnere den 0;
	 */
	function useShared($shared_name) {

		if(!ereg("^[a-z0-9]+$", $shared_name)) {
			trigger_error('Ugyldig shared '.$shared_name, E_USER_ERROR);
		}

		// Tjekker om shared allerede er loaded
		if(!empty($this->shared[$shared_name]) AND is_object($this->shared[$shared_name])) {
			return $this->shared[$shared_name];
		}

		// die($shared_name."ss");

		$main_shared_name = "Shared".ucfirst($shared_name);
		$main_shared_path = PATH_INCLUDE_SHARED.$shared_name."/".$main_shared_name.".php";

		if(file_exists($main_shared_path)) {
			require_once($main_shared_path);
			$object = new $main_shared_name;
			$object->load();
			$this->shared[$shared_name] = $object;
			return $object;
		}
		else {
			trigger_error($main_shared_path.' eksisterer ikke', E_USER_ERROR);
		}
	}

	/*
	function createTranslation($language, $page_id) {


		// set the parameters to connect to your db
		$dbinfo = array(
			'hostspec' => DB_HOST,
			'database' => DB_NAME,
			'phptype'  => 'mysql',
			'username' => DB_USER,
			'password' => DB_PASS
		);

		if (!defined('LANGUAGE_TABLE_PREFIX')) define('LANGUAGE_TABLE_PREFIX', 'core_translation_');

		$params = array(
			'langs_avail_table' => LANGUAGE_TABLE_PREFIX.'langs',
			'strings_default_table' => LANGUAGE_TABLE_PREFIX.'i18n'
		);

		require_once('Translation2/Translation2.php');
		$translation = Translation2::factory('MDB2', $dbinfo, $params);

		//always check for errors. In this examples, error checking is omitted
		//to make the example concise.
		if (PEAR::isError($translation)) {
			trigger_error('Could not start Translation ' . $translation->getMessage(), E_USER_ERROR);
		}

		// set primary language
		$set_language = $translation->setLang($language);

		if (PEAR::isError($set_language)) {
			trigger_error($set_language->getMessage(), E_USER_ERROR);
		}

		// set the group of strings you want to fetch from
		$translation->setPageID($page_id);

		// add a Lang decorator to provide a fallback language
		$translation = $translation->getDecorator('Lang');
		$translation->setOption('fallbackLang', 'uk');

		$translation = $translation->getDecorator('LogMissingTranslation');
		$translation = $translation->getDecorator('DefaultText');

		// %stringID% will be replaced with the stringID
		// %pageID_url% will be replaced with the pageID
		// %stringID_url% will replaced with a urlencoded stringID
		// %url% will be replaced with the targeted url
		//$this->translation->outputString = '%stringID% (%pageID_url%)'; //default: '%stringID%'
		$translation->outputString = '%stringID%';
		$translation->url = '';           //same as default
		$translation->emptyPrefix  = '';  //default: empty string
		$translation->emptyPostfix = '';  //default: empty string

		return $translation;

	}
	*/

	/**
	 * Returns translation object and sets page_id
	 * Could be moved when there is no more calls to the method.
	 */
	function getTranslation($page_id = 'common') {
		if (is_object($this->translation)) {
			if (!empty($page_id)) {
				$this->translation->setPageID($page_id);
			}
			return $this->translation;
		}

		if (isset($this->translation)) {
			$this->translation->setPageID($page_id);
		}

		return $this->translation;
	}

	/**
	 * Implements the observer pattern
	 */
	 /*
	function attach($observer) {
		$this->observers[] = $observer;
	}

	function notify($code, $msg) {
		foreach ($this->getObservers() AS $observer) {
			$observer->update($code, $msg);
		}
	}

	function getObservers() {
		return $this->observers;
	}
	*/

	/**
	 * Function to make a random key - e.g. for passwords
	 * This functions don't return any characters whick can be mistaken.
	 * Won't return 0 (zero) or o (as in Ole) or 1 (one) or l (lars), because they can be mistaken on print.
	 *
	 * @author	Lars Olesen
	 * @version 1.0
	 *
	 * @param $count (integer) how many characters to return?
	 * @return 	random key (string) only letters
	 */
	function randomKey($length = 1)  {
		// Legal characters
		$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789';
		$how_many = strlen($chars);
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;

		while ($i < $length) {
			$num = rand() % $how_many;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}
}


interface Observable {
	function attach($observer);
}

interface Observer {
	function update($code, $msg);
}

class KernelLog implements Observer {
	private $db;
	private $table_name = 'log_table';
	private $table_definition = array(
		'id' => array(
			'type' => 'integer',
			'unsigned' => 1,
			'notnull' => 1,
			'default' => 0
			),
		'logtime' => array(
			'type' => 'timestamp'
			),
		'ident' => array(
			'type' => 'text',
			'length' => 16
			),
		'priority' => array(
			'type' => 'integer',
			'notnull' => 1
			),
		'message' => array(
			'type' => 'text',
			'length' => 200
		)
	);

	private $definition = array('primary' => true, 'fields' => array('id' => array()));

	function __construct () {
		$this->db = MDB2::singleton(DB_DSN);
		if (!$this->tableExists($this->table_name)) {
			$this->createTable();
		}
	}

	function tableExists($table) {
		$this->db->loadModule('Manager', null, true);
		$tables = $this->db->manager->listTables();
		return in_array(strtolower($table), array_map('strtolower', $tables));
	}

	function createTable() {

		$this->db->loadModule('Manager');
		$result = $this->db->createTable($this->table_name, $this->table_definition);

		if (PEAR::isError($result)) {
			die('create ' . $result->getMessage());
		}

		$result = $this->db->createConstraint($this->table_name, 'PRIMARY', $this->definition);
		if (PEAR::isError($result)) {
			die('primary ' . $result->getMessage());
		}
	}

	function update($code, $msg) {
		require_once 'Log.php';
		$log = &Log::singleton('sql', $this->table_name, $code, array('dsn' => DB_DSN, 'sequence' => 'log_id'));
		$log->log($msg);
		return 1;
	}

}

?>