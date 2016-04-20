<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_templatesection_Shorttext extends CMS_TemplateSection
{
    public function __construct($cmspage, $id = 0)
    {
        $this->value['type'] = 'shorttext';
        parent::__construct($cmspage, $id);
    }

    protected function load_section()
    {
        $this->value['size'] = $this->parameter->get('size');
    }

    protected function validate_section($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isNumeric($var['size'], 'error in size', 'allow_empty');

        if ($this->error->isError()) {
            return 0;
        }
        // validere size - men ikke mere end 255
        return 1;
    }

    protected function save_section($var)
    {
        if (empty($var['size'])) {
            $var['size'] = 255;
        }
        $this->addParameter('size', $var['size']);
        return 1;
    }
}
