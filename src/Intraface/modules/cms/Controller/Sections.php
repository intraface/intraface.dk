<?php
class Intraface_modules_cms_Controller_Sections extends k_Component
{
    protected $section_gateway;
    protected $db_sql;
    protected $template;
    public $error = array();

    function __construct(DB_Sql $db, k_TemplateFactory $template)
    {
        $this->db_sql = $db;
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_cms_Controller_Section';
        }
    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');

        /*
        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $identifier_parts = explode(':', $redirect->get('identifier'));
            if ($identifier_parts[0] == 'picture') {
                $section = CMS_Section::factory($this->getKernel(), 'id', $identifier_parts[1]);
                $section->save(array('pic_id' => $redirect->getParameter('file_handler_id')));
            }
            return new k_SeeOther($this->url());
        }
        */

        $cmspage = $this->context->getModel();
        $sections = $cmspage->getSections();

        if (!empty($sections) AND count($sections) == 1 AND array_key_exists(0, $sections) AND $sections[0]->get('type') == 'mixed') {
            return new k_SeeOther($this->url($sections[0]->get('id')));
        };
        if ($this->getKernel()->setting->get('user', 'htmleditor') == 'tinymce') {
            $this->document->addScript('tinymce/jscripts/tiny_mce/tiny_mce.js');
        }

        $data = array(
        	'cmspage' => $cmspage,
        	'sections' => $sections,
            'kernel' => $this->getKernel(),
            'error' => $this->error
        );
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/page');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');

        $files = '';
        if (is_array($this->body('section'))) {
            foreach ($this->body('section') AS $key=>$value) {
                $section = CMS_Section::factory($this->getKernel(), 'id', $key);
                /*
                if ($section->get('type') == 'picture') {
                    if (!empty($_FILES) && !is_array($files)) {
                        $filehandler = new FileHandler($this->getKernel());
                        $filehandler->createUpload();
                        $files = $filehandler->upload->getFiles();
                    }

                    if (is_array($files)) {
                        foreach ($files AS $file) {
                            if ($file->getProp('form_name') == 'new_picture_'.$key) {

                                $filehandler = new FileHandler($this->getKernel());
                                $filehandler->createUpload();
                                $filehandler->upload->setSetting('file_accessibility', 'public');
                                $pic_id = $filehandler->upload->upload($file);

                                if ($pic_id != 0) {
                                    $value['pic_id'] = $pic_id;
                                }
                                // Vi har fundet filen til som passer til dette felt, s� er der ikke nogen grund til at k�re videre.
                                break;
                            }
                        }
                    }

                    if (!isset($value['pic_id'])) {
                         $value['pic_id'] = 0;
                    }
                }
                */
                if (!$section->save($value)) {
                    $this->error[$section->get('id')] = $this->t('error in section') . ' ' . strtolower(implode($section->error->message, ', '));
                }
            }
        }
        if (empty($this->error) AND count($this->error) == 0) {
            if ($this->body('choose_file') && $this->getKernel()->user->hasModuleAccess('filemanager')) {

                // jeg skal bruge array_key, n�r der er klikket p� choose_file, for den indeholder section_id. Der b�r
                // kun kunne v�re en post i arrayet, s� key 0 m� v�re $section_id for vores fil
                $keys = array_keys($_POST['choose_file']);
                $section_id = $keys[0];

                /*
                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                $redirect->setIdentifier('picture:'.$section_id);
                $url = $redirect->setDestination($module_filemanager->getPath().'selectfile', NET_SCHEME . NET_HOST . $this->url());

                $redirect->askParameter('file_handler_id');

                return new k_SeeOther($url);
                */
                return new k_SeeOther($this->url($section_id . '/filehandler/selectfile'));
            } elseif ($this->body('edit_html')) {
                $keys = array_keys($_POST['edit_html']);
                return new k_SeeOther($this->url($keys[0]));
            } elseif ($this->body('close')) {
                return new k_SeeOther($this->url('../../', array('type' => $this->context->getModel()->get('type'), 'id' => $this->context->getModel()->cmssite->get('id'))));
            } else {
                return new k_SeeOther($this->url('../../' . $this->context->getModel()->get('id')));
            }
        } else {
            $cmspage = $section->cmspage;
            $sections = $cmspage->getSections();
            $value = $_POST;
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getSectionGateway()
    {
        if ($this->section_gateway) {
            return $this->section_gateway;
        }
        return $this->section_gateway = new Intraface_modules_cms_SectionGateway($this->getKernel(), $this->db_sql);
    }
}