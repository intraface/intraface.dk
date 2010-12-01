<?php
/**
 * Random picture
 *
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Randompicture extends CMS_Element
{
    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'randompicture';
        parent::__construct($section, $id);
    }

    function load_element()
    {
        $keywords = $this->parameter->get('keywords');
        if (!empty($keywords)) {
            $this->value['keywords'] = unserialize($keywords);
        } else {
            $this->value['keywords'] = array();
        }

        $this->value['size'] = $this->parameter->get('size');
        if (!$size = $this->get('size')) {
            $size = 'medium';
        }

        $this->section->kernel->useModule('filemanager');

        $filemanager = new Intraface_modules_filemanager_Filemanager($this->section->kernel);
        try {
            $img = new Ilib_Filehandler_ImageRandomizer($filemanager, $this->get('keywords'));
            $file = $img->getRandomImage();
            $instance = $file->getInstance($size);
            $this->value['uri'] = $instance->get('file_uri');
            $this->value['height'] = $instance->get('height');
            $this->value['width'] = $instance->get('width');
        } catch (Exception $e) {
            // @todo also choose a standard
            $this->value['uri'] = '';
            $this->value['height'] = '';
            $this->value['width'] = '';
        }
    }

    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);

        /*
        if (!empty($var['pic_text'])) $validator->isString($var['pic_text'], 'error in pic_text', '', 'allow_empty');
        if (!empty($var['pic_id'])) $validator->isNumeric($var['pic_id'], 'error in pic_id', 'allow_empty');
        if (!empty($var['pic_url'])) $validator->isString($var['pic_url'], 'error in pic_url', 'allow_empty');
		*/
        // st�rrelsen skal ogs� valideres

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }


    function save_element($var)
    {
        //$var = array_map('strip_tags', $var);
        $this->parameter->save('keywords', serialize($var['keywords']));
        if (!empty($var['size'])) {
            $this->parameter->save('size', $var['size']);
        }

        return true;
    }
}
