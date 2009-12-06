<?php
class Intraface_modules_cms_Controller_Templates extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_cms_Controller_TemplateEdit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Template';
        }
    }

    function getSiteId()
    {
         return $this->context->name();
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $template = CMS_Template::factory($this->getKernel(), 'id', $_GET['delete']);
            $template->delete();
            $cmssite = $template->cmssite;
            return new k_SeeOther($this->url());
        }
        $cmssite = new CMS_Site($this->getKernel(), $this->getSiteId());
        $template = new CMS_Template($cmssite);


        $templates = $template->getList();

        $data = array(
            'cmssite' => $cmssite,
            'template' => $template,
            'templates' => $templates
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/templates');
        return $tpl->render($this, $data);
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}