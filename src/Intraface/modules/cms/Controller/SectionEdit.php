<?php
class Intraface_modules_cms_Controller_SectionEdit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        }
    }

    function getElement()
    {
        return $element = CMS_Element::factory($this->getKernel(), 'id', $this->name());
    }

    function getFileAppender()
    {
        $this->getKernel()->useShared('filehandler');
        require_once 'Intraface/shared/filehandler/AppendFile.php';
        if ($this->getElement()->get('type') == 'gallery') {
            return $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $this->getElement()->get('id'));
        } elseif ($this->getElement()->get('type') == 'filelist') {
            return $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $this->getElement()->get('id'));
        } elseif ($this->getElement()->get('type') == 'picture') {
             return new AppendFile($this->getKernel(), 'cms_element_picture', $this->getElement()->get('id'));
        }
        throw new Exception('No valid fileappender present');
    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');
        $translation = $this->getKernel()->getTranslation('cms');
        if (is_numeric($this->name())) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $this->name());
            $value = $element->get();

            // til select - denne kan uden problemer fortrydes ved blot at have et link til samme side
            if (isset($_GET['return_redirect_id'])) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
                if ($redirect->get('identifier') == 'picture') {
                    $value['pic_id'] = $redirect->getParameter('file_handler_id');
                } elseif ($redirect->get('identifier') == 'gallery') {
                    $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $element->get('id'));
                    $array_files = $redirect->getParameter('file_handler_id');
                    foreach ($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
                    }
                    $element->load();
                    $value = $element->get();

                } elseif ($redirect->get('identifier') == 'filelist') {
                    $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $element->get('id'));
                    $array_files = $redirect->getParameter('file_handler_id');
                    foreach ($array_files AS $file_id) {
                        $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
                    }
                    $element->load();
                    $value = $element->get();
                }
            } else if (isset($_GET['delete_gallery_append_file_id'])) {
                $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $element->get('id'));
                $append_file->delete((int)$_GET['delete_gallery_append_file_id']);
                $element->load();
                $value = $element->get();
                return new k_SeeOther($this->url());
            } else if (isset($_GET['delete_filelist_append_file_id'])) {
                $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $element->get('id'));
                $append_file->delete((int)$_GET['delete_filelist_append_file_id']);

                $element->load();
                $value = $element->get();
                return new k_SeeOther($this->url());
            }
        } else {
            // der skal valideres noget p� typen ogs�.

            // FIXME ud fra section bliver cms_site loaded flere gange?
            // formentlig har det noget med Template at g�re
            // i �vrigt er tingene alt for t�t koblet i page
            $section = CMS_Section::factory($this->getKernel(), 'id', $this->context->name());
            $element = CMS_Element::factory($section, 'type', $_GET['type']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }

            $value = $element->get();

            $value['type'] = $element->get('type');
            $value['page_id'] = $element->get('page_id');
        }
        if ($this->getKernel()->setting->get('user', 'htmleditor') == 'tinymce') {
            $this->document->addScript('tiny_mce/tiny_mce.js');
        }

        $data = array(
            'value' => $value,
            'element' => $element,
            'kernel' => $this->getKernel(),
            'translation' => $this->getKernel()->getTranslation('cms')
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/section-html-edit');
        return $tpl->render($this, $data);

    }

    function postMultipart()
    {
        $module_cms = $this->getKernel()->module('cms');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');

        $old = true;

        if (!empty($_POST['id'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_POST['id']);
        } else {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['section_id']);
            $element = CMS_Element::factory($section, 'type', $_POST['type']);
            $old = false;
        }

        if ($element->get('type') == 'picture') {
            if (!empty($_FILES['new_pic'])) {

                $filehandler = new FileHandler($this->getKernel());
                $filehandler->createUpload();
                $filehandler->upload->setSetting('file_accessibility', 'public');
                $filehandler->upload->setSetting('allow_only_images', 1);
                if ($filehandler->upload->isUploadFile('new_pic')) {
                    $id = $filehandler->upload->upload('new_pic');
                    if ($id != 0) {
                        $_POST['pic_id'] = $id;
                    }
                }
                $element->error->merge($filehandler->error->getMessage());
            }
        } elseif ($element->get('type') == 'gallery') {

            if (!empty($_FILES['new_pic']) && isset($_POST['upload_new'])) {

                $filehandler = new FileHandler($this->getKernel());
                $filehandler->createUpload();
                $filehandler->upload->setSetting('file_accessibility', 'public');
                $filehandler->upload->setSetting('max_file_size', 5000000);
                $filehandler->upload->setSetting('allow_only_images', 1);
                $id = $filehandler->upload->upload('new_pic');

                // Newly created element which has not been saved yet.
                if ($element->get('id') == 0) {
                    $element->save($_POST);
                }

                if ($id != 0) {
                    $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $element->get('id'));
                    $append_file->addFile($filehandler);
                }
                $element->error->merge($filehandler->error->getMessage());
            }
        } elseif ($element->get('type') == 'filelist') {

            if (!empty($_FILES['new_file']) && isset($_POST['upload_new'])) {
                $filehandler = new FileHandler($this->getKernel());
                $filehandler->createUpload();
                $filehandler->upload->setSetting('file_accessibility', 'public');
                $filehandler->upload->setSetting('max_file_size', 10000000);
                $id = $filehandler->upload->upload('new_file');

                // Newly created element which has not been saved yet.
                if ($element->get('id') == 0) {
                    $element->save($_POST);
                }

                if ($id != 0) {
                    $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $element->get('id'));
                    $append_file->addFile($filehandler);
                }
                $element->error->merge($filehandler->error->getMessage());
            }
        }

        if ($element->save($_POST)) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                if ($element->get('type') == 'picture') {
                    $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('filehandler/selectfile', array('images' => 1)), NET_SCHEME . NET_HOST . $this->url('element/' . $element->get('id')));
                    $redirect->setIdentifier('picture');
                    $redirect->askParameter('file_handler_id');
                    return new k_SeeOther($this->url('../element/' . $element->get('id') . '/filehandler/selectfile', array('images' => 1)));
                } elseif ($element->get('type') == 'gallery') {
                    $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('filehandler/selectfile', array('images' => 1, 'multiple_choice' => true)), NET_SCHEME . NET_HOST . $this->url('element/' . $element->get('id')));
                    $redirect->setIdentifier('gallery');
                    $redirect->askParameter('file_handler_id', 'multiple');
                } elseif ($element->get('type') == 'filelist') {
                    $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('filehandler/selectfile', array('multiple_choice' => true)), NET_SCHEME . NET_HOST . $this->url('element/' . $element->get('id')));
                    $redirect->setIdentifier('filelist');
                    $redirect->askParameter('file_handler_id', 'multiple');
                } else {
                    throw new Exception("Det er ikke en gyldig elementtype til at lave redirect fra");
                }
                return new k_SeeOther($url);
            } elseif (!empty($_POST['close'])) {
                if ($old) {
                    return new k_SeeOther($this->url('../../'));
                } else {
                    return new k_SeeOther($this->url('../../' . $element->section->get('id')));
                }

            } else {
                if ($old) {
                    return new k_SeeOther($this->url('../../element/' . $element->get('id')));
                } else {
                    return new k_SeeOther($this->url('../element/' . $element->get('id')));
                }
            }
        } else {
            $value = $_POST;
        }
        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
