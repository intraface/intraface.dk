<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_templatesection_Picture extends CMS_TemplateSection
{
    function __construct(& $cmspage, $id = 0)
    {
        $this->value['type'] = 'picture';
        parent::__construct($cmspage, $id);
    }

    function load_section()
    {
        $this->value['pic_size'] = $this->parameter->get('pic_size');
    }

    function validate_section($var)
    {
        return true;
    }

    function save_section($var)
    {
        $this->addParameter('pic_size', $var['pic_size']);
        return true;
    }
}
