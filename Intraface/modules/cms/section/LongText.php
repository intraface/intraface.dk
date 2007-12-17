<?php
/**
 * Long Text Section
 *
 * @package Intraface_CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 *
 */

require_once 'HTMLPurifier.php';
require_once dirname(__FILE__) . '/../Section.php';

class CMS_Section_LongText extends CMS_Section {

    var $allowed_tags = '';

    function __construct($cmspage, $id = 0) {
        $this->value['type'] = 'longtext';
        parent::__construct($cmspage, $id);

        // dette skal laves noget smartere
        /*
        foreach ($this->template_section->get('html_format') AS $format) {
            if ($format == 'strong') {
                $this->allowed_tags .= '<strong><b>';
            }
            if ($format == 'em') {
                $this->allowed_tags .= '<em><i>';
            }
            if ($format == 'a') {
                $this->allowed_tags .= '<a>';
            }
        }
        */
    }

    function load_section() {
        $this->value['text'] = $this->parameter->get('text');

        if ($this->parameter->get('saved_with') == 'tinymce') {
            $this->value['html'] = $this->parameter->get('text');
        }
        else {
            $this->value['html'] = autoop($this->parameter->get('text'));
        }

    }


    function validate_section($var) {

        // don't validate if there is no text
        if (empty($var['text'])) return 1;

        $this->allowed_tags = $this->template_section->get('html_format');
        $this->allowed_tags[] = 'p';
        $this->allowed_tags[] = 'br';

        //print_r($this->allowed_tags);

        $validator = new Validator($this->error);
        $validator->isString($var['text'], 'error in text', $this->convertArrayToTags($this->allowed_tags));

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

    function save_section($var) {
        if (empty($var['text'])) $var['text'] = '';
        // only used until we change encoding to utf8
        
        $purifier_cache_dir = PATH_CACHE.'htmlpurifier/';
        if(!is_dir($purifier_cache_dir)) {
            mkdir($purifier_cache_dir);
            if(!is_dir($purifier_cache_dir)) {
                trigger_error('Unable to create HTML Purifier cache dir!', E_USER_ERROR);
                exit;
            }
        }
        
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core', 'Encoding', 'ISO-8859-1');
        $config->set('Cache', 'SerializerPath', $purifier_cache_dir);

        // allowing attributes

        $this->allowed_tags = $this->template_section->get('html_format');
        $this->allowed_tags[] = 'p';

        $config->set('HTML', 'AllowedElements', $this->allowed_tags);
        if (in_array('a', $this->allowed_tags)) {
            $config->set('HTML', 'AllowedAttributes', array('a.href'));
        }

        // starting purifier
        $purifier = new HTMLPurifier($config);
        $clean_text = $purifier->purify($var['text']);

        // should probably purify instead of strip_tags
        $this->addParameter('saved_with', $this->kernel->setting->get('user', 'htmleditor'));
        $this->addParameter('text', $clean_text);
        return 1;
    }

}

?>