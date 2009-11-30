<?php
class Intraface_modules_cms_Controller_TemplateSectionEdit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty ($_GET['id']) AND is_numeric($_GET['id'])) {
            $section = CMS_TemplateSection :: factory($this->getKernel(), 'id', $_GET['id']);
            $value = $section->get();

        } elseif (!empty ($_GET['template_id']) AND is_numeric($_GET['template_id'])) {
            // der skal valideres noget på typen også.

            $template = CMS_Template :: factory($this->getKernel(), 'id', $_GET['template_id']);
            $section = CMS_TemplateSection :: factory($template, 'type', $_GET['type']);

            $value['type'] = $section->get('type');
            $value['template_id'] = $section->get('template_id');

        } else {
            trigger_error('not allowed', E_USER_ERROR);
        }

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template-section-edit');
        return $tpl->render($this);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $template = CMS_Template :: factory($this->getKernel(), 'id', $_POST['template_id']);

        if (!empty ($_POST['id'])) {
            $section = CMS_TemplateSection :: factory($template, 'template_and_id', $_POST['id']);
        } else {
            $section = CMS_TemplateSection :: factory($template, 'type', $_POST['type']);
        }

        if ($section->save($_POST)) {
            if (!empty ($_POST['close'])) {
                header('Location: template.php?id=' . $section->template->get('id'));
                exit;
            } else {
                header('Location: template_section_edit.php?id=' . $section->get('id'));
                exit;
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