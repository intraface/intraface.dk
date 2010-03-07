<?php
class Intraface_modules_cms_Controller_Stylesheet extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('cms');
        $cmssite = $this->getSite();

        $data = array(
            'site_id' => $cmssite->get('id'),
            'css' =>  $cmssite->stylesheet->get('css_own'),
            'cmssite' => $cmssite
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/stylesheet-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('cms');

        $cmssite = $this->getSite();
        if ($cmssite->stylesheet->save($_POST)) {
            if (!empty($_POST['close'])) {
                return new k_SeeOther($this->url('../'));
            } else {
                return new k_SeeOther($this->url());
            }
        }
        return $this->render();
    }

    function getSite()
    {
        return new CMS_Site($this->getKernel(), $this->context->name());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
