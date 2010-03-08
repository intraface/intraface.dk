<?php
class Intraface_modules_cms_Controller_Section extends k_Component
{
    protected $template;
    protected $mdb2;
    protected $section;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function map($name)
    {
        if ($name == 'element') {
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
            $element->getPosition($this->mdb2)->moveToPosition($_GET['moveto']);
            return new k_SeeOther($this->url());
        } elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['delete']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->delete();
            return new k_SeeOther($this->url());
        } elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['undelete']);
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
        if (!empty($_POST['publish'])) {
            $section = $this->getSection();
            if ($section->cmspage->publish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['unpublish'])) {
            $section = $this->getSection();
            if ($section->cmspage->unpublish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['add_element'])) {
            return new k_SeeOther($this->url('element', array('create', 'type' => $_POST['new_element_type_id'])));
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
