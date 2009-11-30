<?php
class Intraface_modules_cms_Controller_SiteEdit extends k_Component
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
        if (is_numeric($this->context->name())) {
            $cmssite = new CMS_Site($this->getKernel(), (int)$this->context->name());
            $value = $cmssite->get();
        } else {
            $cmssite = new CMS_Site($this->getKernel());
            $value = array();
        }

        $data = array(
            'cmssite' => $cmssite,
            'value' => $value,
            'translation' => $this->getKernel()->getTranslation('cms'),
            'cms_module' => $cms_module
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/site-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $cmssite = new CMS_Site($this->getKernel(), (int)$_POST['id']);
        if ($cmssite->save($_POST)) {
            return new k_SeeOther($this->url('../' . $cmssite->get('id')));
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