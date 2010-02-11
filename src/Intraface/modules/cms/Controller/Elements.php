<?php
class Intraface_modules_cms_Controller_Elements extends k_Component
{
    protected $db_sql;
    protected $element_gateway;
    protected $template;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->db_sql = $db;
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Element';
        }
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('../'));
    }

    function renderHtmlCreate()
    {
        $module_cms = $this->getKernel()->module('cms');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $shared_filehandler->includeFile('AppendFile.php');
        $translation = $this->getKernel()->getTranslation('cms');
            // der skal valideres noget p� typen ogs�.

            // FIXME ud fra section bliver cms_site loaded flere gange?
            // formentlig har det noget med Template at g�re
            // i �vrigt er tingene alt for t�t koblet i page
            $element = $this->getElement();
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }

            $value = $element->get();

            $value['type'] = $element->get('type');
            $value['page_id'] = $element->get('page_id');

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

        $element = $this->getElement();

        if ($element->save($_POST)) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                if ($element->get('type') == 'picture') {
                    if ($this->getElement()->get('id') > 0) {
                         return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1)));
                    } else {
                        return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1)));
                    }
                } elseif ($element->get('type') == 'gallery') {
                    if ($this->getElement()->get('id') > 0) {
                         return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1, 'multiple_choice' => 'trupe')));
                    } else {
                        return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1, 'multiple_choice' => 'true')));
                    }
                } elseif ($element->get('type') == 'filelist') {
                    if ($this->getElement()->get('id') > 0) {
                         return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1, 'multiple_choice' => 'true')));
                    } else {
                        return new k_SeeOther($this->url($element->get('id') . '/filehandler/selectfile', array('images' => 1, 'multiple_choice' => 'true')));
                    }

                } else {
                    throw new Exception("Det er ikke en gyldig elementtype til at lave redirect fra");
                }
                return new k_SeeOther($url);
            } elseif (!empty($_POST['close'])) {
                return new k_SeeOther($this->url(null));
            } else {
                return new k_SeeOther($this->url($element->get('id')));
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

    function getElement()
    {
        if ($this->element) {
            return $this->element;
        }

        return $this->element = $this->getElementGateway()->findBySectionAndType($this->context->getSection(), $this->query('type'));
    }

    function getElementGateway()
    {
        if ($this->element_gateway) {
            return $this->element_gateway;
        }
        return $this->element_gateway = new Intraface_modules_cms_ElementGateway($this->getKernel(), $this->db_sql);
    }
}