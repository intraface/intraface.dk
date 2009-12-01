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
            $section = CMS_TemplateSection :: factory($this->getKernel(), 'id', $this->name());
            $value = $section->get();

        } else {
            // der skal valideres noget p� typen ogs�.

            $template = CMS_Template :: factory($this->getKernel(), 'id', $this->context->getTemplateId());
            $section = CMS_TemplateSection :: factory($template, 'type', $_GET['type']);

            $value['type'] = $section->get('type');
            $value['template_id'] = $section->get('template_id');

        }

        $data = array(
            'value' => $value,
            'section' => $section,
            'translation' => $this->getKernel()->getTranslation('cms'),
            'cms_module' => $cms_module
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template-section-edit');
        return $tpl->render($this, $data);
    }

    function postMultipart()
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
                return new k_SeeOther($this->url('../../'));
            } else {
                return new k_SeeOther($this->url());
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