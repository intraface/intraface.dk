<?php
class Intraface_modules_cms_Controller_Template extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_TemplateEdit';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        if (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['movedown']);
            $section->getPosition(MDB2::singleton(DB_DSN))->moveDown();
            $template = $section->template;
        } elseif (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
            $section = CMS_TemplateSection::factory($this->getKernel(), 'id', $_GET['moveup']);
            $section->getPosition(MDB2::singleton(DB_DSN))->moveUp();
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
        $template = CMS_Template::factory($this->getKernel(), 'id', $this->name());

        $sections = $template->getSections();

        $data = array(
            'template'=> $template,
            'sections' => $sections
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');


        if (!empty($_POST['add_section']) AND !empty($_POST['new_section_type'])) {
            $template = CMS_Template::factory($this->getKernel(), 'id', $_POST['id']);
            header('Location: template_section_edit.php?template_id='.$template->get('id').'&type='.$_POST['new_section_type']);
            exit;
        } elseif (!empty($_POST['add_keywords'])) {
            $shared_keyword = $this->getKernel()->useShared('keyword');
            $template = CMS_Template::factory($this->getKernel(), 'id', $_POST['id']);
            header('Location: '.$shared_keyword->getPath().'/connect.php?template_id='.$template->get('id'));
            exit;
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
