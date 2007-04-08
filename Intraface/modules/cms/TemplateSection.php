<?php
/**
 * Der skal kun kunne være en section pr. template_section_id pr. side.
 *
 * @author Lars Olesen <lars@legestue.net>
 * @package CMS
 */
require_once('template_section/ShortText.php');
require_once('template_section/LongText.php');
require_once('template_section/Picture.php');
require_once('template_section/Mixed.php');


class CMS_TemplateSection extends Standard {

	var $id;
	var $kernel;
	var $template;
	var $parameter;
	var $section_types;
	var $value;
	var $error;
	var $position;

	/**
	 * Constructor:
	 * Construktor skal enten have cmspage eller en kernel.
	 * Hvis den får kernel skal den have et id.
	 * Fordelen er, at man ikke behøver at vide hvilken side elementet hører til,
	 * men blot behøver, at have elementid.
	 */

	function CMS_TemplateSection(& $template, $id = 0) {
		CMS_TemplateSection::__construct($template, $id);
	}

	function __construct(& $template, $id = 0) {
		if (!is_object($template) OR strtolower(get_class($template)) != 'cms_template') {
			trigger_error('TemplateSection::__construct skal bruge cmstemplate', FATAL);
		}
		$this->error = new Error;
		$this->id = (int) $id;
		$this->template = & $template;
		$this->kernel = & $template->kernel;

		$this->value['identify_as'] = 'cms_template_section';  // bruges af parameter

		$this->parameter = $this->createParameterObject();

		$cms_module = $this->template->cmssite->kernel->module('cms');
		$this->section_types = $cms_module->getSetting('section_types');

		if (array_key_exists('type', $this->value) AND is_string($this->value['type']) AND in_array($this->value['type'], $this->section_types)) {
			$this->value['type_key'] = array_search($this->value['type'], $this->section_types);
		}

		$this->position = new Position("cms_template_section", "template_id = ".$this->template->get('id')." AND active = 1", "position", "id");

		if ($this->id > 0) {
			$this->load();
		}

	}

	function createParameterObject() {
		return new CMS_Parameter($this);
	}

	function addParameter($key, $value) {
		$this->parameter->save($key, $value);
	}

	function factory($object, $type, $value) {
		$class_prefix = 'CMS_Template_';
		switch ($type) {
			case 'type':
				// validering på value // kun være gyldige elementtyper
				// object skal vre cmspage

				$class = $class_prefix . $value;
				return new $class($object);
				break;
			case 'id':
				// skal bruge kernel og numerisk value
				$cms_module = $object->getModule('cms');
				$section_types = $cms_module->getSetting('section_types');

				$db = new DB_Sql;
				$db->query("SELECT id, type_key, template_id FROM cms_template_section WHERE id = " . $value . " AND intranet_id = " . $object->intranet->get('id'));
				if (!$db->nextRecord()) {
					return false;
				}

				$class = $class_prefix . $section_types[$db->f('type_key')];
				return new $class(CMS_Template::factory($object, 'id', $db->f('template_id')), $db->f('id'));

				break;
			case 'template_and_id':
				// skal bruge cmspage-object og numerisk value id
				$cms_module = $object->kernel->getModule('cms');
				$section_types = $cms_module->getSetting('section_types');

				$db = new DB_Sql;
				$db->query("SELECT id, type_key FROM cms_template_section WHERE id = " . $value . " AND intranet_id = " . $object->kernel->intranet->get('id'));
				if (!$db->nextRecord()) {
					return false;
				}

				$class = $class_prefix . $section_types[$db->f('type_key')];
				return new $class($object, $db->f('id'));

				break;
			default:
				trigger_error('Section::factory::En ugyldig type');
				break;
		}
	}

	function load() {

		$db = new DB_Sql;
		$db->query("SELECT id, name, identifier, type_key, locked FROM cms_template_section WHERE cms_template_section.intranet_id = ".$this->template->cmssite->kernel->intranet->get('id')." AND cms_template_section.id = " . $this->id);
		if (!$db->nextRecord()) {
			return 0;
		}

		$this->value['id'] = $db->f('id');
		$this->value['name'] = $db->f('name');
		$this->value['identifier'] = $db->f('identifier');
		$this->value['type_key'] = $db->f('type_key');
		$this->value['type'] = $this->section_types[$this->value['type_key']];
		$this->value['locked'] = $db->f('locked');

		if (method_exists($this, 'load_section')) {
			$this->load_section();
		}

		return $this->id;
	}

	function validate($var) {

		$validator = new Validator($this->error);
		$validator->isString($var['name'], 'error in name', '', '');

		if (empty($var['identifier'])) {
			$this->error->set('error in identifier - cannot be empty');
		}

		if (!Validate::string($var['identifier'], array('format' => VALIDATE_ALPHA . VALIDATE_NUM . '-_'))) {
			$this->error->set('error in identfier - allowed characters are a-z and 1-9');
		}
		if ($this->isIdentifierUnique($var['identifier'])) {
			$this->error->set('error in identifier - has to be unique');

		}

		if ($this->error->isError()) {
			return 0;
		}


		return 1;
	}

	function save($var) {
		$var['identifier'] = trim($var['identifier']);
		if (!isset($var['locked'])) $var['locked'] = 0;

		if (!$this->validate($var)) {
			return 0;
		}
		$db = new DB_Sql;

		if ($this->id == 0) {
			$sql_type = "INSERT INTO ";
			$sql_end = ", date_created = NOW()";
		}
		else {
			$sql_type = "UPDATE ";
			$sql_end = " WHERE id = " . $this->id;
		}
		$sql = $sql_type . " cms_template_section SET
				name = '".safeToDb($var['name'])."',
				identifier = '".safeToDb($var['identifier'])."',
				site_id = " . $this->template->cmssite->get('id') . ",
				intranet_id = ".$this->template->kernel->intranet->get('id').",
				template_id = ".$this->template->get('id').",
				type_key = ".$this->value['type_key'].",
				date_updated = NOW(), locked = ".safeToDb($var['locked'])."
			" . $sql_end;

		$db->query($sql);

		if ($this->id == 0) {
			$this->id = $db->insertedId();
			$this->position->moveToMax($this->id);
		}

		$this->load();

		$this->parameter = new CMS_Parameter($this);

		if (!$this->validate_section($var)) {
			return 0;
		}

		$this->save_section($var);

		return $this->id;
	}

	function delete() {
		$db = new DB_Sql;
		$db->query("UPDATE cms_template_section SET active = 0 WHERE id = " . $this->id);
		return 1;
	}



	function getList() {
		$db = new DB_Sql;
		$db->query("SELECT id, name, identifier, type_key FROM cms_template_section WHERE template_id = " . $this->template->get('id') . " AND intranet_id = " . $this->kernel->intranet->get('id') . " AND active = 1 ORDER BY position ASC");

		$i = 0;
		$sections = array();
		while ($db->nextRecord()) {
			$sections[$i]['id'] = $db->f('id');
			$sections[$i]['name'] = $db->f('name');
			$sections[$i]['identifier'] = $db->f('identifier');
			$sections[$i]['type_key'] = $db->f('type_key');
			$sections[$i]['type'] = $this->section_types[$db->f('type_key')];
			$i++;
		}
		return $sections;
	}

	/**
	 * Følgende to funktioner kan jeg vel egentlig bare slette og bruge position
	 *
	 */

	function moveUp() {
		$this->position->moveUp($this->id);
	}

	function moveDown() {
		$this->position->moveDown($this->id);
	}

	function isIdentifierUnique($identifier) {
		$db = new DB_Sql;
		$db->query("SELECT count(*) AS antal FROM cms_template_section WHERE identifier = '".$identifier."' AND intranet_id = " . $this->kernel->intranet->get('id') . " AND template_id = " . $this->template->get('id') . " AND active = 1 AND id != " . $this->id);
		if (!$db->nextRecord()) {
			return 0;
		}
		return $db->f('antal');

	}
}

?>