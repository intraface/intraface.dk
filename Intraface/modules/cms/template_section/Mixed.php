<?php

/**
 * Denne skal klargøre et billede
 * Størrelsen på billedet sættes i denne
 */

class CMS_Template_Mixed extends CMS_TemplateSection {

	function __construct(& $cmspage, $id = 0) {
		$this->value['type'] = 'mixed';
		parent::__construct($cmspage, $id);
	}

	function load_section() {

		if ($this->parameter->get('allowed_element')) {
			$this->value['allowed_element'] = unserialize($this->parameter->get('allowed_element'));
		}
		else {
			$this->value['allowed_element'] = array();
		}
	}

	function validate_section(& $var) {
		return 1;
	}

	function save_section($var) {
		return $this->addParameter('allowed_element', serialize($var['allowed_element']));
	}

}

?>