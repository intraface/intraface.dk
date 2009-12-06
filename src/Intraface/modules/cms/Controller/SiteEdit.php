<?php
class Intraface_modules_cms_Controller_SiteEdit extends k_Component
{
    protected $template;
    protected $cmssite;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getCMSSite()
    {
        if (is_object($this->cmssite)) return $this->cmssite;
        return $this->cmssite = new CMS_Site($this->getKernel(), (int)$this->context->name());
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');
        if (is_numeric($this->context->name())) {
            $cmssite = $this->getCMSSite();
            $value = $cmssite->get();
        } else {
            $cmssite = $this->getCMSSite();
            $value = array();
        }

        if ($this->body()) {
            $value = $this->body();
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

        $cmssite = $this->getCMSSite();
        if ($id = $cmssite->save($_POST)) {
            if (is_numeric($this->context->name())) {
                return new k_SeeOther($this->context->url());
            } else {
                return new k_SeeOther($this->context->url($id));
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