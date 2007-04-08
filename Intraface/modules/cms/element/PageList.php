<?php

class CMS_Pagelist extends CMS_Element {

	function __construct(& $section, $id = 0) {
		$this->value['type'] = 'pagelist';
		parent::__construct($section, $id);
		//$this->section->kernel->useShared('filehandler');

	}

	function validate_element($var) {
		$validator = new Validator($this->error);
		$validator->isString($var['headline'], 'error in headline', '', 'allow_empty');
		$validator->isString($var['show_type'], 'error in Show type', '', 'allow_empty');
		//$validator->isString($var['show_keyword'], 'error in show keyword', '', 'allow_empty');
		$validator->isString($var['show'], 'error in show', '', 'allow_empty');
		//$validator->isString($var['lifetime'], 'error in lifetime', '', 'allow_empty');


		if ($this->error->isError()) {
			return 0;
		}

		return 1;
	}




	function save_element($var) {

		$this->parameter->save('headline', $var['headline']);
		$this->parameter->save('show_type', $var['show_type']);
		settype($var['keyword'], 'array');
		$this->parameter->save('keyword', serialize($var['keyword']));
		$this->parameter->save('show', $var['show']);
		//$this->parameter->save('lifetime', $var['lifetime']);
		return 1;
	}
	function load_element() {

		$this->value['headline'] = $this->parameter->get('headline');
		$this->value['show_type'] = $this->parameter->get('show_type');
		$this->value['keyword'] = unserialize($this->parameter->get('keyword'));
		$this->value['show'] = $this->parameter->get('show');
		//$this->value['lifetime'] = $this->parameter->get('lifetime');


		$this->section->cmspage->dbquery->clearAll();
		if (!empty($this->value['show_type'])) {
			$this->section->cmspage->dbquery->setFilter('type', $this->value['show_type']);
		}
		if (!empty($this->value['keyword'])) {
			$this->section->cmspage->dbquery->setKeyword($this->value['keyword']);
		}

		$this->value['pages'] = $this->section->cmspage->getList();

	}


}

?>