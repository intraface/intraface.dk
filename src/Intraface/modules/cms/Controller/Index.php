<?php
class Intraface_modules_cms_Controller_Index extends k_Component
{
    protected $template;
    protected $db_sql;
    protected $site_gateway;
    protected $site;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->template = $template;
        $this->db_sql = $db;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Site';
        } elseif ($name == 'create') {
            return 'Intraface_modules_cms_Controller_SiteEdit';
        }
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');

        $sites = $this->getSiteGateway()->getAll();

        $data = array(
            'sites' => $sites
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function renderHtmlCreate()
    {
        $cms_module = $this->getKernel()->module('cms');
        $value = array();

        if ($this->body()) {
            $value = $this->body();
        }

        $data = array(
            'cmssite' => $this->getEmptySite(),
            'value' => $value,
            'cms_module' => $cms_module
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/site-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');

        if ($id = $this->getEmptySite()->save($_POST)) {
            return new k_SeeOther($this->url($id));
        } else {
            $value = $_POST;
        }
        return $this->render();
    }

    function getEmptySite()
    {
        if (is_object($this->site)) {
            return $this->site;
        }
        return $this->site = $this->getSiteGateway()->getEmptySite();
    }

    function getSiteGateway()
    {
        if (is_object($this->site_gateway)) {
            return $this->site_gateway;
        }
        return $this->site_gateway = new Intraface_modules_cms_SiteGateway($this->getKernel(), $this->db_sql);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
