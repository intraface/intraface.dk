<?php
/**
 * @todo Hvordan skal vi lige lave det med st�rrelser. Skal man ikke bare kunne v�lge st�rrelse
 * fra FileHandler-st�rrelserne?
 *
 * Hvis skal f�lgende �ndres section_html_edit.php, hvor den skal tage st�rrelserne.
 * Validering af i validate_element();
 *
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Picture extends CMS_Element
{
    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'picture';
        parent::__construct($section, $id);
        $this->section->kernel->useModule('filemanager');
    }

    function load_element()
    {
        $this->section->kernel->useModule('filemanager');
        $this->value['pic_id'] = $this->parameter->get('pic_id');
        $this->value['pic_size'] = $this->parameter->get('pic_size');
        $this->value['pic_text'] = $this->parameter->get('pic_text');
        $this->value['pic_url'] = $this->parameter->get('pic_url');
        if (!$size = $this->get('pic_size')) {
            $size = 'medium';
        }

        if (!empty($this->value['pic_id'])) {
            $filemanager = new FileHandler($this->section->kernel, $this->get('pic_id'));
            if ($filemanager->get('id') > 0) {
                if ($size == 'original') {
                    $this->value['picture'] = $filemanager->get();
                } else {
                    $filemanager->createInstance($size);
                    $this->value['picture'] = $filemanager->instance->get();
                }
            }
        }

    }

    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);

        if (!empty($var['pic_text'])) {
            $validator->isString($var['pic_text'], 'error in pic_text', '', 'allow_empty');
        }
        //if (!empty($var['pic_id'])) $validator->isNumeric($var['pic_id'], 'error in pic_id', 'allow_empty');
        if (!empty($var['pic_url'])) {
            $validator->isString($var['pic_url'], 'error in pic_url', 'allow_empty');
        }

        // st�rrelsen skal ogs� valideres

        if ($this->error->isError()) {
            return false;
        }

        return true;
    }


    function save_element($var)
    {
        $var = array_map('strip_tags', $var);
        //$var = safeToDb($var);
        /*
        if (!empty($_FILES)) {
            $filehandler = new FileHandler($this->section->kernel);
            $filehandler->loadUpload();
            $filehandler->upload->setSetting('max_file_size', 5000000);
            $filehandler->upload->setSetting('file_accessibility', 'public');
            if (!$var['pic_id'] = $filehandler->upload->upload('userfile')) {
                throw new Exception('Kunne ikke uploade filen');
            }
            if (!empty($var['pic_text'])) {
                if ($this->section->kernel->user->hasModuleAccess('filemanager')) {
                    $this->section->kernel->useModule('filemanager');
                    $filemanager = new FileManager($this->section->kernel, $var['pic_id']);
                    if (!$filemanager->update(array('description' => $var['pic_text']))) {
                        throw new Exception('Filemanager kunne ikke gemme teksten.');
                    }
                }
            }
        }
        else {
            $var['pic_id'] = $this->parameter->get('pic_id');
        }
        */

        if (isset($var['pic_id'])) {
            $this->parameter->save('pic_id', $var['pic_id']);
        }
        if (!empty($var['pic_size'])) {
            $this->parameter->save('pic_size', $var['pic_size']);
        }
        if (!empty($var['pic_text'])) {
            $this->parameter->save('pic_text', $var['pic_text']);
        }
        if (!empty($var['pic_url'])) {
            $this->parameter->save('pic_url', $var['pic_url']);
        }

        return true;
    }
}
