<?php
/**
 * @package Intraface_CMS
 */
class CMS_Template_Picture extends CMS_TemplateSection {

    function __construct(& $cmspage, $id = 0) {
        $this->value['type'] = 'picture';
        parent::__construct($cmspage, $id);
    }

    function load_section() {
        $this->value['pic_size'] = $this->parameter->get('pic_size');
    }

    function validate_section(& $var) {
        return 1;
    }

    function save_section($var) {
        $this->addParameter('pic_size', $var['pic_size']);
        return 1;
    }

}

?>