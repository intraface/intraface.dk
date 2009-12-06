<?php
class Intraface_modules_cms_Controller_PageEdit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $this->getKernel()->useShared('filehandler');
        if (is_numeric($this->context->name())) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $this->context->name());
            $cmssite = $cmspage->cmssite;

            $value = $cmspage->get();
            $type = $value['type'];
            $template = $cmspage->template;

            // til select - denne kan uden problemer fortrydes ved blot at have et link til samme side
            if (!empty($_GET['return_redirect_id']) AND is_numeric($_GET['return_redirect_id'])) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
                $value['pic_id'] = $redirect->getParameter('file_handler_id');
            }
        } elseif (!empty($_GET['type'])) {

            $type = $_GET['type'];
            $value['type'] = $type;
            $cmssite = new CMS_Site($this->getKernel(), $this->context->getSiteId());
            $cmspage = new CMS_Page($cmssite);
            $value['site_id'] = $this->context->getSiteId();
            $template = new CMS_Template($cmssite);
        } else {
            trigger_error(__('not allowed', 'common'), E_USER_ERROR);
        }


        if (!empty($type)) {
            $page_types = CMS_Page::getTypesWithBinaryIndex();
            $binary_bage_type = array_search($type, $page_types);
        }
        else {
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

    function postMultipart()
    {
        $module_cms = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $this->getKernel()->useShared('filehandler');

        $cmssite = new CMS_Site($this->getKernel(), $_POST['site_id']);
        $cmspage = new CMS_Page($cmssite, $_POST['id']);

        if (!empty($_FILES['new_pic'])) {
            $filehandler = new FileHandler($this->getKernel());
            $filehandler->createUpload();
            $filehandler->upload->setSetting('file_accessibility', 'public');
            $id = $filehandler->upload->upload('new_pic');

            if ($id != 0) {
                $_POST['pic_id'] = $id;
            }
        }

        if ($cmspage->save($_POST)) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_cms->getPath().'page_edit.php?id='.$cmspage->get('id'));
                $redirect->askParameter('file_handler_id');
                return new k_SeeOther($url);
            } elseif (!empty($_POST['close'])) {
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../../' . $cmspage->get('id')));
                } else {
                    return new k_SeeOther($this->url('../' . $cmspage->get('id')));
                }

            } elseif (!empty($_POST['add_keywords'])) {
                $keyword_shared = $this->getKernel()->useShared('keyword');
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../keyword/connect'));
                } else {
                    return new k_SeeOther($this->url('../' . $cmspage->get('id') . '/keyword/connect'));
                }
            } else {
                return new k_SeeOther($this->url(null));
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
}