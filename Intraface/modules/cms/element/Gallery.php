<?php
/**
 * @package Intraface_CMS
 */
require_once 'Intraface/modules/cms/Element.php';

class CMS_Gallery extends CMS_Element {

    var $methods = array('single_image');

    function __construct($section, $id = 0) {
        $this->value['type'] = 'gallery';

        parent::__construct($section, $id);
        $this->section->kernel->useShared('filehandler');
    }


    function load_element() {
        $this->value['pictures'] = array();
        $this->value['gallery_select_method'] = $this->parameter->get('gallery_select_method');
        $this->value['thumbnail_size'] = $this->parameter->get('thumbnail_size');
        $this->value['show_description'] = $this->parameter->get('show_description');
        if (empty($this->value['show_description'])) {
            $this->value['show_description'] = 'hide';
        }

        if (empty($this->value['thumbnail_size'])) {
            $this->value['thumbnail_size'] = 3;
        }
        $this->value['popup_size'] = $this->parameter->get('popup_size');
        if (empty($this->value['popup_size'])) {
            $this->value['popup_size'] = 4;
        }

        if(false) { // benytter keyword
        /*
            // Dette skal lige implementeres, s� hvis man har filemanager, og har benyttet n�gleord, s�
            // skal array returneres ved hj�lp af Filemanager. V�r opm�rksom p� hvis en bruger der ikke har
            // Filemanager ser elementet, lavet af en der har filemanager. her i vis, skal der nok overrules om
            // brugeren har filemanager.
            $this->value['keyword_id'] = $this->parameter->get('keyword_id');

            $filemanager = new FileManager($this->kernel);
            $filemanager->dbquery->setKeyword($this->value['keyword_id']);
            $files = $filemanager->getList();

        */
        }
        else { // Enkeltfiler
            // print("this->id i gallery: ".$this->id."<br /><br />");
            $shared_filehandler = $this->kernel->useShared('filehandler');
            $shared_filehandler->includeFile('AppendFile.php');
            $append_file = new AppendFile($this->kernel, 'cms_element_gallery', $this->id);
            $append_file->createDBQuery();
            $append_file->dbquery->setFilter('order_by', 'name');
            $files = $append_file->getList();
        }
        // print_r($files);
        // print("<br /><br />");
        $i = 0;
        foreach ($files AS $file) {

            if(isset($file['file_handler_id'])) {
                $id = $file['file_handler_id'];
                $append_file_id = $file['id'];
            }
            else {
                $id = $file['id'];
                $append_file_id = 0;
            }

            $filehandler = new FileHandler($this->kernel, $id);
            if ($filehandler->value['file_type']['image'] == 0) {
                continue;
            }

            $filehandler->createInstance();
            $this->value['pictures'][$i] = $filehandler->get();
            $this->value['pictures'][$i]['instances'] = $filehandler->instance->getList();
            $this->value['pictures'][$i]['append_file_id'] = $append_file_id;
            // $this->value['pictures'][$i]['show_uri'] = $file_uri;

            $i++;
        }
    }


    function validate_element($var) {
        $validator = new Validator($this->error);
        $validator->isString($var['gallery_select_method'], 'error in gallery_select_method');

        if (!in_array($var['gallery_select_method'], $this->methods)) {
            $this->error->set('error in gallery_select_method');
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    function save_element($var) {
        // Der skal gemmes om man benytter keyword eller enkeltfiler.
        // $this->parameter->save('keyword_id', $var['keyword_id']);
        $this->parameter->save('gallery_select_method', $var['gallery_select_method']);
        $this->parameter->save('thumbnail_size', intval($var['thumbnail_size']));
        $this->parameter->save('popup_size', intval($var['popup_size']));
        $this->parameter->save('show_description', $var['show_description']);

        return true;
    }
    /*
    function getElementHtml() {
        $filemanager = new FileHandler($this->kernel);
        $filemanager->dbquery->setKeyword($this->value['keyword_id']);
        $files = $filemanager->getList();

        $output  = '<div class="cms-gallery">';

        foreach ($files AS $file) {
            $fm = new FileHandler($this->kernel, $file->get('id'));
            if ($fm->value['file_type']['image'] == 0) {
                continue;
            }

            $fm->loadInstance('small');
            $thumb_uri = $fm->instance->get('file_uri');
            $fm->loadInstance('medium');
            $file_uri = $fm->instance->get('file_uri');

            $output .= '<div class="cms-gallery-item">';
            $output .= '	<a href="'.$file_uri .'">';
            $output .= '		<img src="'.$thumb_uri.'" alt="" />';
            $output .= '	</a>';
            $output .= '</div>';

        }

        $output .= '</div>';

        return $output;
    }
    */

}

?>