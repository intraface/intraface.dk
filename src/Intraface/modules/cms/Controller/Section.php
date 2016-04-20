<?php
class Intraface_modules_cms_Controller_Section extends k_Component
{
    protected $template;
    protected $mdb2;
    protected $section;
    protected $db_sql;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2, DB_Sql $db)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
        $this->db_sql = $db;
    }

    function map($name)
    {
        if ($name == 'element') {
            return 'Intraface_modules_cms_Controller_Elements';
        } elseif ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        }
    }

    function appendFile($file_id)
    {
        $section = CMS_Section::factory($this->getKernel(), 'id', $this->name());
        $section->save(array('pic_id' => $file_id));
        return true;
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $element_types = $cms_module->getSetting('element_types');
        $translation = $this->getKernel()->getTranslation('cms');

        if ($this->getSection()->get('type') != 'mixed') {
            return new k_SeeOther($this->context->url());
        }

        if (is_numeric($this->query('moveto'))) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $this->query('element_id'));
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->getPosition($this->db_sql)->moveToPosition($this->query('moveto'));
            return new k_SeeOther($this->url());
        } elseif (is_numeric($this->query('delete'))) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $this->query('delete'));
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->delete();
            return new k_SeeOther($this->url());
        } elseif (is_numeric($this->query('undelete'))) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $this->query('undelete'));
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->undelete();
            return new k_SeeOther($this->url());
        }

        $this->document->addScript('getElementsBySelector.js');
        $this->document->addScript('cms/section_html.js');

        $data = array(
            'section' => $this->getSection(),
            'translation' => $translation,
            'element_types' => $element_types
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/section-html');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        if ($this->body('publish')) {
            $section = $this->getSection();
            if ($section->cmspage->publish()) {
                return new k_SeeOther($this->url());
            }
        } elseif ($this->body('unpublish')) {
            $section = $this->getSection();
            if ($section->cmspage->unpublish()) {
                return new k_SeeOther($this->url());
            }
        } elseif ($this->body('add_element')) {
            return new k_SeeOther($this->url('element', array('create', 'type' => $this->body('new_element_type_id'))));
        }

        return $this->render();
    }

    function getSection()
    {
        if ($this->section) {
            return $this->section;
        }
        return $this->section = $this->context->getSectionGateway()->findById($this->name());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
