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
        if ($name == 'section') {
            return 'Intraface_modules_cms_Controller_TemplateSections';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('cms');

        if (!empty($_GET['movedown']) and is_numeric($_GET['movedown'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['movedown']);
            $section->getPosition($this->mdb2)->moveDown();
            $template = $section->template;
        } elseif (!empty($_GET['moveup']) and is_numeric($_GET['moveup'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['moveup']);
            $section->getPosition($this->mdb2)->moveUp();
            $template = $section->template;
        }

        if (!empty($_GET['delete']) and is_numeric($_GET['delete'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['delete']);
            $section->delete();
            $template = $section->template;
        } elseif (!empty($_GET['undelete']) and is_numeric($_GET['undelete'])) {
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

    function renderHtmlEdit()
    {
        $data = array(
            'template' => $this->getModel(),
            'value' => $this->getModel()->get(),
            'translation' => $this->getKernel()->getTranslation()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->getKernel()->module('cms');

        $template = $this->getModel();

        if ($id = $this->getModel()->save($_POST)) {
            if (!empty($_POST['close'])) {
                return new k_SeeOther($this->context->url());
            } else {
                return new k_SeeOther($this->url());
            }
        } else {
            $value = $_POST;
            $value['for_page_type'] = array_sum($_POST['for_page_type']);
        }
        return $this->render();
    }

    function renderHtmlDelete()
    {
        $this->getModel()->delete();
        return new k_SeeOther($this->url('../'));
    }

    function getTemplateGateway()
    {
        return $this->context->getTemplateGateway();
    }

    function getModel()
    {
        if ($this->cms_template) {
            return $this->cms_template;
        }
        return $this->cms_template = $this->getTemplateGateway()->findById($this->name());
    }

    function putForm()
    {
        $this->getKernel()->module('cms');

        if (!empty($_POST['add_section']) and !empty($_POST['new_section_type'])) {
            return new k_SeeOther($this->url('section/create', array('type' => $_POST['new_section_type'])));
            exit;
        } elseif (!empty($_POST['add_keywords'])) {
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
