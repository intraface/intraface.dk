<?php
/**
 * @package Intraface_CMS
 */
class CMS_ShortText extends CMS_Element {

    function __construct(& $cmspage, $id = 0) {
        $this->value['type'] = 'shorttext';
        parent::__construct($cmspage, $id);
    }

    function load_element() {
        $this->value['text'] = $this->parameter->get('text');
    }

    function validate_element(& $var) {
        // template_section er ikke oprettet ved nye sider
        // is_object($this->template_section) AND
        if (strlen($var['text']) > $this->template_section->get('size')) {
            // der er skrevet flere bogstaver end der må være
            $this->error->set('error in text - you wrote to many characters');
        }

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function save_element($var) {
        $var['text'] = safeToDb($var['text']);
        $var['text'] = strip_tags($var['text']);
        return $this->parameter->save('text', $var['text']);
    }

}

?>