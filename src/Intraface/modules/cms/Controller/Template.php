<?php
class Intraface_modules_cms_Controller_Template extends k_Component
{
    protected $template;
    protected $mdb2;
    protected $cms_template;
    protected $db_sql;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2, DB_Sql $db)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
        $this->db_sql = $db;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_TemplateEdit';
        } elseif ($name == 'section') {
            return 'Intraface_modules_cms_Controller_TemplateSections';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('cms');

        if (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['movedown']);
            $section->getPosition($this->mdb2)->moveDown();
            $template = $section->template;
        } elseif (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['moveup']);
            $section->getPosition($this->mdb2)->moveUp();
            $template = $section->template;
        }

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['delete']);
            $section->delete();
            $template = $section->template;
        } elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['undelete']);
            $section->undelete();
            $template = $section->template;
        }

        $data = array(
            'template'=> $this->getModel(),
            'sections' => $this->getModel()->getSections()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template');
        return $tpl->render($this, $data);
    }

    function getTemplateGateway()
    {
        return new Intraface_modules_cms_TemplateGateway($this->getKernel(), $this->db_sql);
    }

    function getModel()
    {
        if ($this->cms_template) {
            return $this->cms_template;
        }
        return $this->cms_template = $this->getTemplateGateway()->findById($this->name());
    }

    function postForm()
    {
        $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');


        if (!empty($_POST['add_section']) AND !empty($_POST['new_section_type'])) {
            $template = CMS_Template::factory($this->getKernel(), 'id', $_POST['id']);
            return new k_SeeOther($this->url('section/create', array('type' => $_POST['new_section_type'])));
            exit;
        } elseif (!empty($_POST['add_keywords'])) {
            $shared_keyword = $this->getKernel()->useShared('keyword');
            $template = CMS_Template::factory($this->getKernel(), 'id', $_POST['id']);
            return new k_SeeOther($this->url('keyword/connect'));
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getSiteId()
    {
        return $this->context->getSiteId();
    }
}
