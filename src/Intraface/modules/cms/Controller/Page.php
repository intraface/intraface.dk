<?php
class Intraface_modules_cms_Controller_Page extends k_Component
{
    protected $template;
    public $error = array();

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_PageEdit';
        } elseif ($name == 'section') {
            return 'Intraface_modules_cms_Controller_Sections';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }

    /**
     * @see Intraface_Keyword_Controller_Index
     * @return object
     */
    function getModel()
    {
        return $cmspage = $this->context->getPageGateway()->findById($this->name());
    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $identifier_parts = explode(':', $redirect->get('identifier'));
            if ($identifier_parts[0] == 'picture') {
                $section = CMS_Section::factory($this->getKernel(), 'id', $identifier_parts[1]);
                $section->save(array('pic_id' => $redirect->getParameter('file_handler_id')));
            }
            return new k_SeeOther($this->url());

        }

        $cmspage = $this->getModel();
        $sections = $cmspage->getSections();

        if (!empty($sections) AND count($sections) == 1 AND array_key_exists(0, $sections) AND $sections[0]->get('type') == 'mixed') {
            return new k_SeeOther($this->url('section/' . $sections[0]->get('id')));
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

    function postForm()
    {
        $module_cms = $this->getKernel()->module('cms');

        if (!empty($_POST['publish'])) {
            if ($this->getModel()->publish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['unpublish'])) {
            if ($this->getModel()->unpublish()) {
                return new k_SeeOther($this->url());
            }
        }
        return $this->render();
    }

    function postMultipart()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');

        $files = '';
        if (isset($_POST['section']) && is_array($_POST['section'])) {
            foreach ($_POST['section'] AS $key=>$value) {
                $section = CMS_Section::factory($this->getKernel(), 'id', $key);

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
                if (!$section->save($value)) {
                    $this->error[$section->get('id')] = __('error in section') . ' ' . strtolower(implode($section->error->message, ', '));
                }
            }
        }
        if (empty($this->error) AND count($this->error) == 0) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {

                // jeg skal bruge array_key, n�r der er klikket p� choose_file, for den indeholder section_id. Der b�r
                // kun kunne v�re en post i arrayet, s� key 0 m� v�re $section_id for vores fil
                $keys = array_keys($_POST['choose_file']);
                $section_id = $keys[0];

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                $redirect->setIdentifier('picture:'.$section_id);
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $this->url());

                $redirect->askParameter('file_handler_id');
                return new k_SeeOther($url);
            } elseif (!empty($_POST['edit_html'])) {
                $keys = array_keys($_POST['edit_html']);
                return new k_SeeOther($this->url('section/' . $keys[0]));
            } elseif (!empty($_POST['close'])) {
                return new k_SeeOther($this->url('../', array('type' => $this->getModel()->get('type'), 'id' => $this->getModel()->cmssite->get('id'))));
            } else {
                return new k_SeeOther($this->url('../' . $this->getModel()->get('id')));
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
}
