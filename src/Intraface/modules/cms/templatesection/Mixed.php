<?php
/**
 * Denne skal klargøre et billede
 * Størrelsen på billedet sættes i denne
 * @package Intraface_CMS
 */
class Intraface_modules_cms_templatesection_Mixed extends CMS_TemplateSection
{
    function __construct($cmspage, $id = 0)
    {
        $this->value['type'] = 'mixed';
        parent::__construct($cmspage, $id);
    }

    function load_section()
    {
        if ($this->parameter->get('allowed_element')) {
            $this->value['allowed_element'] = unserialize($this->parameter->get('allowed_element'));
        } else {
            $this->value['allowed_element'] = array();
        }
    }

    function validate_section($var)
    {
        return true;
    }

    function save_section($var)
    {
        return $this->addParameter('allowed_element', serialize($var['allowed_element']));
    }
}
