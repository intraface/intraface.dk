<?php
class Intraface_modules_shop_Controller_Categories_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_Categories_Edit';
        } elseif ($name == 'filehandler') {
            return 'Intraface_Filehandler_Controller_Index';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle($this->getModel()->getName());

        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/category');
        return $tpl->render($this);
    }

    function renderHtmlEdit()
    {
        $this->document->setTitle('Edit category');
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $data = array(
            'category_object' => $this->getModel(),
            'regret_link' => $redirect->getRedirect($this->url('../'))
        );
        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/categories-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if (!$this->isValid()) {
            throw new Exception('Values not valid');
        }
        try {
            $category = $this->getModel();
            $category->setIdentifier($this->body('identifier'));
            $category->setName($this->body('name'));
            $category->setParentId($this->body('parent_id'));
            $category->save();
        } catch (Exception $e) {
            throw $e;
        }

        $url = $redirect->getRedirect($this->context->url());

        return new k_SeeOther($redirect->getRedirect($url));
    }

    function isValid()
    {
        $error = new Intraface_Error();
        $validator = new Intraface_Validator($error);
        $validator->isString($this->body('name'), 'category name is not valid');
        $validator->isString($this->body('identifier'), 'category identifier is not valid');
        $validator->isNumeric($this->body('parent_id'), 'category parent id has to be numeric');
        return !$error->isError();
    }

    function getModel($id = 0)
    {
        if ($id == 0) {
            $id = $this->name();
        }
        return $this->context->getModel($id);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFileAppender()
    {
        $module = $this->getKernel()->useModule('filemanager');
        require_once 'Intraface/modules/filemanager/AppendFile.php';
        return new AppendFile($this->getKernel(), 'category', $this->getModel()->getId());
    }

    function getPictures()
    {
        // @todo The fileappender should know which files are appended
        //       and know that this only takes one file.
        $module = $this->getKernel()->useModule('filemanager');
        require_once 'Intraface/modules/filemanager/AppendFile.php';

        $pictures = array();
        $append_file = new AppendFile($this->getKernel(), 'category', $this->getModel()->getId());
        $appendix_list = $append_file->getList();
        foreach ($appendix_list as $picture) {
            $pictures[] = new Ilib_Filehandler($this->getKernel(), $picture['file_handler_id']);
        }
        return $pictures;
    }
}
