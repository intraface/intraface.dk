<?php
class Intraface_modules_cms_Controller_Templates extends k_Component
{
    protected $template;
    protected $db_sql;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->template = $template;
        $this->db_sql = $db;
    }

    function map($name)
    {
        if (is_numeric($name)) {
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

    function renderHtmlCreate()
    {
        $cmssite = new CMS_Site($this->getKernel(), $this->getSiteId());
        $template = new CMS_Template($cmssite);
        $value['site_id'] = $this->getSiteId();
        $value['for_page_type'] = 7; // all types;

        $data = array(
            'template' => $template,
            'value' => $value,
            'translation' => $this->getKernel()->getTranslation()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('cms');

        $cmssite = new CMS_Site($this->getKernel(), $this->getSiteId());
        $template = new CMS_Template($cmssite);

        if ($id = $template->save($_POST)) {
            if (empty($_POST['close'])) {
                return new k_SeeOther($this->url($id));
            } else {
                return new k_SeeOther($this->url($id));
            }
        } else {
            $value = $_POST;
            $value['for_page_type'] = array_sum($_POST['for_page_type']);
        }
        return $this->render();
    }

    function getTemplateGateway()
    {
        return new Intraface_modules_cms_TemplateGateway($this->getKernel(), $this->db_sql);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}