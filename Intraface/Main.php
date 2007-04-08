<?php

/**
 *
 * Usage:
 * <code>
 * <?php

class MainExample Extends Main {

	function MainExample() {
		$this->module_name = "example"; // Navn på på mappen med modullet
		$this->menu_label = "Eksempel"; // Navn er det skal stå i menuen
		$this->show_menu = 1; // Skal modullet være vist i menuen
		$this->active = 1; // Er modullet aktivt

		// Tilføjer et undermenupunkt
		$this->addSubMenuItem("Underside", "underside.php");
		// Tilføjer undermenupunkt, der kun vises når hvis man har sub_acces'en vat_report
		$this->addSubMenuItem("Moms", "vat.php", "sub_access:canCreate");
		// Tilføjer undermenupunkt, der kun vises når hvis man har adgang til modullet backup
		$this->addSubMenuItem("Årsafslutning", "end.php", "module:backup");

		// Tilføjer en subaccess
		$this->addSubAccessItem("canCreate", "Rettighed til at oprette");

		// Tilføjer en setting, som er ens for alle intranet. Se længere nede
		$this->addSetting("payment_method", array("Dankort", "Kontant");

		// Filer der skal inkluderes ved opstart af modul.
		$this->addPreloadFile("fil.php");

		// Fil til med indstillinger man kan sætte i modullet
		$this->addControlpanelFile('Regnskab', '/modules/accounting/setting.php');

		// Fil der inkluderes på forsiden.
		$this->addFrontpageFile('include_front.php');

		// Inkluder fil med definition af indstillinger. Bemærk ikke den sammme indstilling som addSetting(). Filen skal indeholde følgende array: $_setting["modul_navn.setting"] = "Værdi";
		$this->includeSettingFile("settings.php");

		// Dependent module vil automatisk blive inkluderet på siden. (Hvis man ikke har rettighed til det vil der komme en fejl)
		$this->addDependentModule("pdf");

		// Inkludere et shared i modullet.
		$this->addRequiredShared("filehandler");
	}
}


SETTING:
Setting kan bruges til at sætte indstillinger, som er ens for alle intranet.
En setting kan hentes igen ved hjælp af $module_object->getSetting("payment_method")



 * </code>
 *
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */

class Main {

	var $menu_label;
	var $show_menu;
	var $active;
	var $menu_index;
	var $frontpage_index;
	var $submenu = array();
	var $sub_access = array();
	var $sub_access_description = array();
	var $preload_file = array();
	var $dependent_module = array();
	var $required_shared = array();
	var $module_name;
	var $setting;
	var $controlpanel_files;
	var $frontpage_files; // til brug på forsiden
	var $translation;
	var $kernel;


	function Main() {
		// init
		$this->module_name = '';
		$this->menu_label = '';
		$this->show_menu = 0;
		$this->active = 0;
		$this->menu_index = 0;
		$this->frontpage_index = 0;
	}



	/**
	 * Denne funktion køres af kernel, når man loader modulet
	 *
	 */

	function load(&$kernel) {
		// Inkluder preload filerne
		$this->kernel = &$kernel;

		if(is_array($this->required_shared) && count($this->required_shared) > 0) {
			foreach($this->required_shared AS $shared_name) {

				$this->kernel->useShared($shared_name);
			}
		}



		for($i = 0, $max = count($this->preload_file); $i<$max; $i++) {
			$this->includeFile($this->preload_file[$i]);
		}

	}

	/**
	 * Denne funktion bruges af MainModulnavn.php til at fortælle, hvor includefilen til
   * det enkelte modul ligger.
	 */
	function addFrontpageFile($filename) {
		$this->frontpage_files[] = $filename;
	}

	function getFrontpageFiles() {
		return $this->frontpage_files;
	}

	function addControlpanelFile($title, $url) {
		$this->controlpanel_files[] = array(
			'title' => $title,
			'url' => $url
		);
	}

	function getControlpanelFiles() {
		return $this->controlpanel_files;
	}


	/**
	 * Tilføjer et undermenu punkt
	 * Benyttes fra
	 */
	function addSubmenuItem($label, $url, $sub_access = '') {
		$i = count($this->submenu);
		$this->submenu[$i]['label'] = $label;
		$this->submenu[$i]['url'] = $url;
		$this->submenu[$i]['sub_access'] = $sub_access;
	}

	function getSubmenu() {
		return($this->submenu);
	}

	function addSubAccessItem($name, $description) {
		array_push($this->sub_access, $name);
		array_push($this->sub_access_description, $description);
	}

	function addPreloadFile($file) {
		$this->preload_file[] = $file;
	}

	/**
   * Bruges til at inkludere fil
   *
   * Ændret med at tjekke om filen eksisterer.
   */
	function includeFile($file) {
		$file = PATH_INCLUDE_MODULE.$this->module_name."/".$file;
		if (!file_exists($file)) {
			return 0;
		}
		require_once($file);
		return 1;
	}

	/**
	 *  Inkluderer automatisk et andet modul. Man skal dog have adgang til det andet modul.
	 */
	function addDependentModule($module) {
		$this->dependent_module[] = $module;
	}

	function getDependentModules() {
		return $this->dependent_module;
	}

	/**
	 * Giver mulighed for at inkludere shared der skal benyttes overalt i modullet.
	 *
	 */
	function addRequiredShared($shared) {
		$this->required_shared[] = $shared;
	}

	function getRequiredShared() {
		return $this->required_shared;

		// print_r($this->required_shared);
		// die();
	}


	function includeSettingFile($file) {
		global $_setting; // den globaliseres også andre steder?
		include(PATH_INCLUDE_MODULE.$this->module_name."/".$file);
	}

	function getPath() {
		return(PATH_WWW_MODULE.$this->module_name."/");
	}

  /*
	// sikkert overflødig
	function getValidationPath() {
		return(INCLUDE_MODULE.$this->module_name."/validationrules/");
	}
	*/

	/**
	 * Følgende to funktioner bør vel droppes?
	 */

	function getId() {
		$db = new DB_Sql;
		$db->query("SELECT id FROM module WHERE name = '".$this->module_name."'");
		if ($db->nextRecord()) return $db->f('id');
		return 0;
	}


	function getName() {
		return($this->module_name);
	}

	/**
   * Bruges til at tilføje en setting til et modul, som skal være hardcoded ind i Main[Modulnavn]
   */
	function addSetting($key, $value) {
		$this->setting[$key] = $value;
	}

	function getSetting($key) {
		if(isset($this->setting[$key])) {
			return($this->setting[$key]);
		}
	}
  /*
  function addTranslation($shortterm, $translation) {
    $this->translation[$shortterm] = $translation;
  }

	function getTranslation($shortterm) {
		if (!empty($this->translation[$shortterm])) {
			return $this->translation[$shortterm];
		}
		return $shortterm;
	}
  */
}

?>
