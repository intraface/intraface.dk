<?php
/**
 * Picture Section
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once dirname(__FILE__) . '/../Section.php';

class CMS_Section_Picture extends CMS_Section {

	function __construct($cmspage, $id = 0) {
		$this->value['type'] = 'picture';
		parent::__construct($cmspage, $id);
		$this->cmspage->kernel->useShared('filehandler');

	}

	function load_section() {

		$this->value['pic_id'] = $this->parameter->get('pic_id');
		$size = $this->template_section->get('pic_size');
		$this->cmspage->kernel->useShared('filehandler');
		$this->value['picture'] = array();

		if($this->value['pic_id'] == 0) return;


		$filemanager = new FileHandler($this->cmspage->kernel, $this->value['pic_id']);

		if ($filemanager->get('id') > 0) {
			if ($size == 'original') {
				$this->value['picture'] = $filemanager->get();
			}
			else {
				$filemanager->loadInstance($size);
				$this->value['picture'] = $filemanager->instance->get();

			}
		}
	}

	function validate_section(& $var) {
		$validator = new Validator($this->error);
		if (!empty($var['pic_id'])) $validator->isNumeric($var['pic_id'], 'error in pic_id', 'allow_empty');

		if ($this->error->isError()) {
			return 0;
		}
		return 1;
	}

	function save_section($var) {

		/*
		Det gøres nu i page.php
		if (!empty($_FILES['new_picture_'.$key])) {
			$filehandler = new FileHandler($kernel);
			$filehandler->loadUpload();
			$filehandler->upload->setSetting('file_accessibility', 'public');
			$id = $filehandler->upload->upload('new_picture_'.$key);

			if($id != 0) {
				$var['pic_id'] = $id;
			}
		}
		*/

		//print_r($_FILES);
		/*
		if (!empty($var['delete_picture'])) {
			$var['pic_id'] = 0;
		}
		elseif (!empty($_FILES) AND !empty($_FILES['userfile']['name'][$this->get('id')])) {
			$filehandler = new FileHandler($this->cmspage->kernel);
			$filehandler->loadUpload();
			if(!$id = $filehandler->upload->upload('userfile['.$this->get('id').']')) {
				trigger_error('Kunne ikke uploade filen');
			}
			$var['pic_id'] = $id;
			if ($this->cmspage->kernel->user->hasModuleAccess('filemanager')) {
				$this->cmspage->kernel->useModule('filemanager');
				$filemanager = new FileManager($this->cmspage->kernel, $var['pic_id']);
				if (!$filemanager->update(array('description' => $var['pic_text']))) {
					trigger_error('Filemanager kunne ikke gemme teksten.');
				}
			}

		}
		else {
			$var['pic_id'] = $this->parameter->get('pic_id');
		}
		*/
		if (!empty($var['pic_id'])) $this->addParameter('pic_id', $var['pic_id']);
		return 1;

	}
}

?>