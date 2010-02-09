<?php
class Intraface_modules_cms_Controller_Pages extends k_Component
{
    protected $template;
    protected $page_gateway;
    protected $db_sql;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, DB_Sql $db, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->db_sql = $db;
        $this->mdb2 = $mdb2;
    }

    function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_cms_Controller_PageEdit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Page';
        }
    }

    function getPageGateway()
    {
        if (is_object($this->page_gateway)) {
            return $this->page_gateway;
        }

        return $this->page_gateway = new Intraface_modules_cms_PageGateway($this->getKernel(), $this->db_sql);
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
            $cmspage = $this->getPageGateway()->findById($_GET['moveup']);
            $cmspage->getPosition($this->mdb2)->moveUp();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } elseif (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
            $cmspage = $this->getPageGateway()->findById($_GET['movedown']);
            $cmspage->getPosition($this->mdb2)->moveDown();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $cmspage = $this->getPageGateway()->findById($_GET['delete']);
            $cmspage->delete();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } else {
            if (empty($_GET['type']) || !in_array($_GET['type'], CMS_Page::getTypes())) {
                trigger_error('A valid type of page is needed', E_USER_ERROR);
            } else {
                $type = $_GET['type'];
            }

            if (is_numeric($this->context->name())) {
                $cmssite = new CMS_Site($this->getKernel(), (int)$this->context->name());
                $cmspage = new CMS_Page($cmssite);
                $this->getKernel()->setting->set('user', 'cms.active.site_id', (int)$this->name());

            } else {
                $site_id = $this->getKernel()->setting->get('user', 'cms.active.site_id');
                if ($site_id != 0) {
                    $cmssite = new CMS_Site($this->getKernel(), $site_id);
                    $cmspage = new CMS_Page($cmssite);
                } else {
                    return new k_SeeOther($this->url('../'));
                }
            }

        }

        $page_types_plural = CMS_Page::getTypesPlural();

        $this->document->addScript('yui/connection/connection-min.js');
        $this->document->addScript('cms/checkboxes.js');
        $this->document->addScript('cms/publish.js');

        $data = array(
        	'type' => $type,
        	'page_types_plural' => $page_types_plural,
            'cmssite' => $cmssite,
            'cmspage' => $cmspage);

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/pages');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');

        foreach ($_POST['page'] AS $key=>$value) {

            $cmssite = new CMS_Site($this->getKernel(), $_POST['id']);
            $cmspage = new CMS_Page($cmssite, $_POST['page'][$key]);
            if ($cmspage->setStatus($_POST['status'][$key])) {
            }

        }

        if (isAjax()) {
            echo 1;
            exit;
        } else {
            return new k_SeeOther($this->url($cmssite->get('id')));
        }

        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getSiteId()
    {
        return $this->context->name();
    }
}
