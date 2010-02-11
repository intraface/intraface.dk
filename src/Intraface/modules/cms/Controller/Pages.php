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
        if (is_numeric($name)) {
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
        } /*elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $cmspage = $this->getPageGateway()->findById($_GET['delete']);
            $cmspage->delete();
            $cmssite = $cmspage->cmssite;
            $type = $cmspage->get('type');
        } */else {
            if (empty($_GET['type']) || !in_array($_GET['type'], CMS_Page::getTypes())) {
                return new k_SeeOther($this->url(null, array('type' => 'page', 'flare'=>'A valid type of page is needed')));
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

        $cmspage = new CMS_Page($cmssite);
        $cmspage->getDBQuery()->setFilter('type', 'page');
        $cmspage->getDBQuery()->setFilter('level', 'alllevels');
        $pages = $cmspage->getList('page', 'alllevels');

        $cmsarticles = new CMS_Page($cmssite);
        $cmsarticles->getDBQuery()->setFilter('type', 'article');
        $articles = $cmsarticles->getList();

        $cmsnews = new CMS_Page($cmssite);
        $cmsnews->getDBQuery()->setFilter('type', 'news');
        $news = $cmsnews->getList();

        $data = array(
        	'type' => $type,
        	'page_types_plural' => $page_types_plural,
            'cmssite' => $cmssite,
            'cmspage' => $cmspage,
            'pages' => $pages,
            'articles' => $articles,
            'news' => $news
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/pages');
        return $tpl->render($this, $data);
    }

    function renderHtmlCreate()
    {
        $module_cms = $this->getKernel()->module('cms');

        $type = $_GET['type'];
        $value['type'] = $type;
        $cmssite = $this->context->getSite();
        $cmspage = new CMS_Page($cmssite);
        $value['site_id'] = $this->context->getSite()->get('id');
        $template = new CMS_Template($cmssite);


        if (!empty($type)) {
            $page_types = CMS_Page::getTypesWithBinaryIndex();
            $binary_bage_type = array_search($type, $page_types);
        } else {
            trigger_error('no type is given!', E_USER_ERROR);
            exit;
        }

        $templates = $template->getList($binary_bage_type);
        $cmspages = $cmspage->getList();

        $this->document->addScript('cms/page_edit.js');
        $this->document->addScript('cms/parseUrlIdentifier.js');

        $data = array('value' => $value,
        	'type' => $type,
        	'cmspage' => $cmspage,
        	'template' => $template,
            'translation' => $this->getKernel()->getTranslation('cms'),
            'templates' => $templates,
            'cmssite' => $cmssite,
            'kernel' => $this->getKernel(),
            'cmspages' => $cmspages);

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/page-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module_cms = $this->getKernel()->module('cms');

        $cmssite = $this->context->getSite();
        $cmspage = new CMS_Page($cmssite);

        if ($cmspage->save($_POST)) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
                return new k_SeeOther($this->url($cmspage->get('id') . '/filehandler/selectfile', array('images' => 1)));
            } elseif (!empty($_POST['close'])) {
                return new k_SeeOther($this->url($cmspage->get('id')));
            } elseif (!empty($_POST['add_keywords'])) {
                $keyword_shared = $this->getKernel()->useShared('keyword');
                return new k_SeeOther($this->url($cmspage->get('id') . '/keyword/connect'));
            } else {
                return new k_SeeOther($this->url(null, array('type' => $cmspage->get('type'))));
            }
        } else {
            $value = $_POST;
            $type = $_POST['page_type'];
            $template = $cmspage->template;
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
