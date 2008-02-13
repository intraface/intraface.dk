<?php
/**
 * @package Intraface_CMS
 */

require_once 'HTMLPurifier.php';
require_once 'Intraface/modules/cms/Element.php';

class CMS_LongText extends CMS_Element
{
    private $allowed_tags = '';

    function __construct(& $section, $id = 0)
    {
        $this->value['type'] = 'longtext';
        parent::__construct($section, $id);
    }

    function load_element()
    {
        $this->value['text'] = $this->parameter->get('text');

        if ($this->parameter->get('saved_with') == 'tinymce') {
            $this->value['html'] = $this->parameter->get('text');
        } else {
            $this->value['html'] = autoop($this->parameter->get('text'));
        }

    }

    function validate_element($var)
    {
        // don't validate if there is no text
        if (empty($var['text'])) {
            return true;
        }

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }

    public static function convertArrayToTags($array)
    {
        $tags = '';
        foreach ($array AS $tag) {
            $tags .= '<'.$tag.'>';
        }
        return $tags;
    }

    function save_element($var)
    {
        $purifier = new HTMLPurifier();
        $clean_text = $purifier->purify($var['text']);

        // should probably purify instead of strip_tags
        $this->parameter->save('saved_with', $this->kernel->setting->get('user', 'htmleditor'));
        $this->parameter->save('text', $clean_text);
        return true;
    }
}