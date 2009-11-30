<?php
class Intraface_modules_cms_Controller_Pages extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_cms_Controller_Page';
    }

    function renderHtml()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');


        if (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $_GET['moveup']);
            $cmspage->getPosition(MDB2::singleton(DB_DSN))->moveUp();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } elseif (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $_GET['movedown']);
            $cmspage->getPosition(MDB2::singleton(DB_DSN))->moveDown();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $_GET['delete']);
            $cmspage->delete();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } else {
            if (empty($_GET['type']) || !in_array($_GET['type'], CMS_Page::getTypes())) {
                trigger_error('A valid type of page is needed', E_USER_ERROR);
            } else {
                $type = $_GET['type'];
            }

            if (!empty($_GET['id'])) {
                $cmssite = new CMS_Site($this->getKernel(), (int)$_GET['id']);
                $cmspage = new CMS_Page($cmssite);
                $this->getKernel()->setting->set('user', 'cms.active.site_id', (int)$_GET['id']);
            } else {
                $site_id = $this->getKernel()->setting->get('user', 'cms.active.site_id');
                if ($site_id != 0) {
                    $cmssite = new CMS_Site($this->getKernel(), $site_id);
                    $cmspage = new CMS_Page($cmssite);
                } else {
                    header('location: index.php');
                    exit;
                }
            }

        }

        $page_types_plural = CMS_Page::getTypesPlural();

        $this->document->addScript($this->url('/yui/connection/connection-min.js'));
        $this->document->addScript($this->url('/cms/checkboxes.js'));
        $this->document->addScript($this->url('/cms/publish.js'));

        $data = array();

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/pages');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $cms_module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

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
            header('Location: site.php?id='.$cmssite->get('id'));
            exit;
        }

        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
