<?php
/**
 * @package Intraface_CMS
 */

require_once 'HTMLPurifier.php';

class CMS_LongText extends CMS_Element {

    var $allowed_tags = '';

    function __construct(& $section, $id = 0) {
        $this->value['type'] = 'longtext';
        parent::__construct($section, $id);
    }

    function load_element() {
        $this->value['text'] = $this->parameter->get('text');

        if ($this->parameter->get('saved_with') == 'tinymce') {
            $this->value['html'] = $this->parameter->get('text');
        }
        else {
            $this->value['html'] = autoop($this->parameter->get('text'));
        }

    }


    function validate_element($var) {

        // don't validate if there is no text
        if (empty($var['text'])) return 1;
        /*
        $this->allowed_tags = $this->template_section->get('html_format');
        $this->allowed_tags[] = 'p';
        $this->allowed_tags[] = 'br';

        //print_r($this->allowed_tags);

        $validator = new Validator($this->error);
        $validator->isString($var['text'], 'error in text', $this->convertArrayToTags($this->allowed_tags));
        */
        // if error return 0
        if ($this->error->isError()) {
            return 0;
        }

        return 1;
    }

    function convertArrayToTags($array) {
        $tags = '';
        foreach ($array AS $tag) {
            $tags .= '<'.$tag.'>';
        }
        return $tags;
    }

    function save_element($var) {
        /*
        // only used until we change encoding to utf8
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core', 'Encoding', 'ISO-8859-1');

        // allowing attributes

        $this->allowed_tags = $this->template_section->get('html_format');
        $this->allowed_tags[] = 'p';

        $config->set('HTML', 'AllowedElements', $this->allowed_tags);
        if (!in_array('a', $this->allowed_tags)) {
            $config->set('HTML', 'AllowedAttributes', array('a.href'));
        }
        */
        // starting purifier
        //$purifier = new HTMLPurifier($config);
        $purifier = new HTMLPurifier();
        $clean_text = $purifier->purify($var['text']);

        // should probably purify instead of strip_tags
        $this->parameter->save('saved_with', $this->kernel->setting->get('user', 'htmleditor'));
        $this->parameter->save('text', $clean_text);
        return 1;
    }

}

?>