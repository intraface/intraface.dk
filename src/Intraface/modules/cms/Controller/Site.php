<?php
class Intraface_modules_cms_Controller_Site extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'stylesheet') {
            return 'Intraface_modules_cms_Controller_Stylesheet';
        } elseif ($name == 'templates') {
            return 'Intraface_modules_cms_Controller_Templates';
        } elseif ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_SiteEdit';
        }  elseif ($name == 'pages') {
            return 'Intraface_modules_cms_Controller_Pages';
        }

    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $cmssite = new CMS_Site($this->getKernel(), (int)$this->name());
        $cmspage = new CMS_Page($cmssite);

        $data = array(
            'cmssite' => $cmssite,
            'cmspage' => $cmspage,
            'kernel' => $this->getKernel()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/site');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}

