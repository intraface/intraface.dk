<?php
class Intraface_modules_cms_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
         if (is_numeric($name)) {
             return 'Intraface_modules_cms_Controller_Site';
         }  elseif ($name == 'create') {
            return 'Intraface_modules_cms_Controller_SiteEdit';
        }

    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $site = new CMS_Site($this->getKernel(), $_GET['delete']);
            if (!$site->delete()) {
                throw new Exception('The site could not be deleted');
            }
        }

        $site = new CMS_Site($this->getKernel());
        $sites = $site->getList();

        $data = array(
            'site' => $site,
            'sites' => $sites
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}