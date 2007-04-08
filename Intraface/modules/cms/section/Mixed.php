<?php
/**
 * Mixed Section
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once dirname(__FILE__) . '/../Section.php';

class CMS_Section_Mixed extends CMS_Section {

	function __construct($cmspage, $id = 0) {
		$this->value['type'] = 'mixed';
		parent::__construct($cmspage, $id);
	}

	function load_section() {
		//$this->value['html'] = $this->getSectionHtml();
		foreach ($this->getElements() AS $element) {
			$this->value['elements'][] = $element->get();
		}
		return 1;

	}

	function validate_section(& $var) {
		return 1;
	}

	function save_section($var) {
		return 1;
	}

	/**
	 * FIXME - tror den her er med til at forårsage mange sql kald
	 *
	 */
	function getElements() {
		$element = array();
		$sql_expire = '';
		$sql_publish = '';
		if (!is_object($this->kernel->user)) {
			$sql_expire = " AND (date_expire > NOW() OR date_expire = '0000-00-00 00:00:00')";
			$sql_publish = " AND date_publish < NOW()";
		}


		$db = new DB_Sql;
		$db->query("SELECT id FROM cms_element
			WHERE intranet_id = ".$this->kernel->intranet->get('id')."
				AND section_id = " . $this->id . "
				AND active = 1 " . $sql_expire . $sql_publish . "
			ORDER BY position ASC");
		$i = 0;

		while ($db->nextRecord()) {
			$element[$i] = CMS_Element::factory($this, 'section_and_id', $db->f('id'));
			$i++;
		}

		return $element;
	}
	/*
	function getSectionHtml($type = '') {
		$elements = $this->getElements();
		$display = '';

		if (is_array($elements) AND count($elements) > 0) {
			foreach ($elements AS $key => $element) {
				$display .= $element->display($type);
				// 'Position: ' . $element->get('position') . '<br>' . $element->get('id') . '<br>' .
			}
		}

		return $display;
	}
	*/



}

?>