<?php
class Intraface_modules_cms_Controller_Element extends k_Component
{
    protected $template;
    protected $element;

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

    function dispatch()
    {
        if (!$this->getElement()) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function getElement()
    {
        if (is_object($this->element)) {
            return $this->element;
        }

        return $this->element = $this->context->getElementGateway()->findById($this->name());
    }

    function getFileAppender()
    {
        $this->getKernel()->useModule('filemanager');
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

    /**
     * @see Intraface_Filehandler_Controller_SelectFile::postForm
     *
     * @param integer $file_id
     *
     * @return boolean
     */
    function appendFile($file_id)
    {
        if ($this->getElement()->get('type') == 'picture') {
            $value = $this->getElement()->get();
            $value['pic_id'] = $file_id;
            if (!$this->getElement()->save($value)) {
                echo $this->getElement()->error->view();
            }
        } elseif ($this->getElement()->get('type') == 'gallery') {
            $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $this->getElement()->get('id'));
            $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
        } elseif ($this->getElement()->get('type')) {
            $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $this->getElement()->get('id'));
            $append_file->addFile(new FileHandler($this->getKernel(), $file_id));
        }
        return true;
    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        if (isset($_GET['remove_gallery_append_file_id'])) {
            $append_file = new AppendFile($this->getKernel(), 'cms_element_gallery', $this->getElement()->get('id'));
            $append_file->delete((int)$_GET['remove_gallery_append_file_id']);
            return new k_SeeOther($this->url());
        } else if (isset($_GET['remove_filelist_append_file_id'])) {
            $append_file = new AppendFile($this->getKernel(), 'cms_element_filelist', $this->getElement()->get('id'));
            $append_file->delete((int)$_GET['remove_filelist_append_file_id']);
            return new k_SeeOther($this->url());
        }

        $element = $this->getElement();
        $value = $element->get();

        $this->document->addScript('ckeditor/ckeditor.js');
        $this->document->addScript('ckeditor/lang/_languages.js');

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
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $shared_filehandler->includeFile('AppendFile.php');

        $element = $this->getElement();

        if ($element->save($_POST)) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                if ($element->get('type') == 'picture') {
                    return new k_SeeOther($this->url('filehandler/selectfile', array('images' => 1)));
                } elseif ($element->get('type') == 'gallery') {
                     return new k_SeeOther($this->url('filehandler/selectfile', array('images' => 1, 'multiple_choice' => 1)));
                } elseif ($element->get('type') == 'filelist') {
                     return new k_SeeOther($this->url('filehandler/selectfile', array('images' => 0, 'multiple_choice' => 1)));
                } else {
                    throw new Exception("Det er ikke en gyldig elementtype til at lave redirect fra");
                }
                return new k_SeeOther($url);
            } elseif (!empty($_POST['close'])) {
                return new k_SeeOther($this->url('../../'));
            } else {
                return new k_SeeOther($this->url('../../element/' . $element->get('id')));
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

    function renderHtmlDelete()
    {
        throw new Exception('her');
        $this->getElement()->delete();
        return new k_SeeOther($this->url('../'));
    }
}
