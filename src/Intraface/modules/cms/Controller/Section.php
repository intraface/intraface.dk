<?php
class Intraface_modules_cms_Controller_Section extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
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
            header('Location: section_html.php?id='.$section->get('id'));
            exit;

        } elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['delete']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->delete();
            $section = $element->section;
            header('Location: section_html.php?id='.$section->get('id'));
            exit;

        } elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
            $element = CMS_Element::factory($this->getKernel(), 'id', $_GET['undelete']);
            if (!is_object($element)) {
                throw new Exception('Unable to create a valid element object');
            }
            $element->undelete();
            $section = $element->section;
            header('Location: section_html.php?id='.$section->get('id'));
            exit;
        } elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_GET['id']);
        }
        $this->document->addScripts($this->url('/getElementsBySelector.js'));
        $this->document->addScripts($this->url('/cms/section_html.js'));


        $tpl = $this->template->create(dirname(__FILE__) . '/templates/section-html');
        return $tpl->render($this);
    }

    function postForm()
    {
        if (!empty($_POST['publish'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            if ($section->cmspage->publish()) {
                header('location: section_html.php?id='.$section->get('id'));
                exit;
            }
        } elseif (!empty($_POST['unpublish'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            if ($section->cmspage->unpublish()) {
                header('location: section_html.php?id='.$section->get('id'));
                exit;
            }

        } elseif (!empty($_POST['add_element'])) {
            $section = CMS_Section::factory($this->getKernel(), 'id', $_POST['id']);
            header('Location: section_html_edit.php?section_id='.$section->get('id').'&type='.$_POST['new_element_type_id']);
            exit;
        }

        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
