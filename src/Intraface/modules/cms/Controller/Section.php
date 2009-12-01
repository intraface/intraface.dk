<?php
class Intraface_modules_cms_Controller_Section extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_SectionEdit';
        } elseif ($name == 'element') {
            return 'Intraface_modules_cms_Controller_Elements';
        }
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $element_types = $cms_module->getSetting('element_types');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty($_GET['moveto']) AND is_numeric($_GET['moveto'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['element_id']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->getPosition(MDB2::singleton(DB_DSN))->moveToPosition($_GET['moveto']);
            $section = $element->section;
            return new k_SeeOther($this->url());
        } elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['delete']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->delete();
            $section = $element->section;
            return new k_SeeOther($this->url());
        } elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['undelete']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->undelete();
            $section = $element->section;
            return new k_SeeOther($this->url());
        }

        $section = CMS_Section::factory($this->getKernel(), 'id', $this->name());

        $this->document->addScript($this->url('/getElementsBySelector.js'));
        $this->document->addScript($this->url('/cms/section_html.js'));

        $data = array(
            'section' => $section,
            'translation' => $translation,
            'element_types' => $element_types
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/section-html');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        if (!empty($_POST['publish'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            if ($section->cmspage->publish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['unpublish'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            if ($section->cmspage->unpublish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['add_element'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            return new k_SeeOther($this->url('edit', array('type' => $_POST['new_element_type_id'])));
        }

        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
