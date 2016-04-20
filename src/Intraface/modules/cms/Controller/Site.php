<?php
class Intraface_modules_cms_Controller_Site extends k_Component
{
    protected $template;
    protected $site;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getSite()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'stylesheet') {
            return 'Intraface_modules_cms_Controller_Stylesheet';
        } elseif ($name == 'templates') {
            return 'Intraface_modules_cms_Controller_Templates';
        } elseif ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_SiteEdit';
        } elseif ($name == 'pages') {
            return 'Intraface_modules_cms_Controller_Pages';
        } elseif ($name == 'navigation') {
            return 'Intraface_modules_cms_Controller_Navigation';
        }
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');

        $cmspage = new CMS_Page($this->getSite());

        $data = array(
            'cmssite' => $this->getSite(),
            'cmspage' => $cmspage,
            'kernel' => $this->getKernel()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/site');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $cms_module = $this->getKernel()->module('cms');
        $value = $this->getSite()->get();

        if ($this->body()) {
            $value = $this->body();
        }

        $data = array(
            'cmssite' => $this->getSite(),
            'value' => $value,
            'cms_module' => $cms_module,
            'translation' => $this->getKernel()->getTranslation('cms')
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/site-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');

        if ($id = $this->getSite()->save($_POST)) {
            return new k_SeeOther($this->url());
        } else {
            $value = $_POST;
        }
        return $this->render();
    }

    function renderHtmlDelete()
    {
        if (!$this->getSite()->delete()) {
            throw new Exception('The site could not be deleted');
        }
        return new k_SeeOther($this->url('../'));
    }

    function getSite()
    {
        if (is_object($this->site)) {
            return $this->site;
        }
        return $this->site = new CMS_Site($this->getKernel(), (int)$this->name());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
