<?php
/**
 * Mixed Section
 *
 * @package Intraface_CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_modules_cms_section_Shorttext extends CMS_Section
{
    function __construct($cmspage, $id = 0)
    {
        $this->value['type'] = 'shorttext';
        parent::__construct($cmspage, $id);
    }

    function load_section()
    {
        $this->value['text'] = $this->parameter->get('text');
    }

    function validate_section($var)
    {
        if (!empty($var['text']) AND strlen($var['text']) > $this->template_section->get('size')) {
            $this->error->set('error in text - you wrote to many characters');
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function save_section($var)
    {
        if (empty($var['text'])) $var['text'] = '';
        $var['text'] = strip_tags($var['text']);
        return $this->addParameter('text', $var['text']);
    }
}
