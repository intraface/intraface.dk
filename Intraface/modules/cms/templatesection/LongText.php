<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_templatesection_LongText extends CMS_TemplateSection
{
    private $possible_allowed_html = array(
        'strong', 'a', 'em'
    );

    function __construct($cmspage, $id = 0)
    {
        $this->value['type'] = 'longtext';
        parent::__construct($cmspage, $id);
    }

    function getAllowedHTMLOptions()
    {
        return $this->possible_allowed_html;
    }

    function load_section()
    {
        $this->value['size'] = $this->parameter->get('size');
        if ($this->parameter->get('html_format')) {
            $this->value['html_format'] = unserialize($this->parameter->get('html_format'));
            if (!is_array($this->value['html_format'])) {
                $this->value['html_format'] = array();
            }
        } else {
            $this->value['html_format'] = array();
        }
    }

    function validate_section($var)
    {
        $validator = new Intraface_Validator($this->error);
        if (!empty($var['size'])) {
            $validator->isNumeric($var['size'], 'error in size', 'allow_empty');
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function save_section($var)
    {
        if (empty($var['html_format'])) {
            $var['html_format'] = array();
        }
        if (empty($var['size'])) {
            $var['size'] = 1000000;
        }
        $this->addParameter('size', $var['size']);
        $this->addParameter('html_format', serialize($var['html_format']));
        return true;
    }
}