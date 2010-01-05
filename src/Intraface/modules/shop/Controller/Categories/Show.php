<?php
class Category_Appender extends Ilib_Filehandler_AppendFile
{
    function __construct($kernel, $belong_to, $belong_to_id)
    {
        $this->registerBelongTo(9, 'category');
        parent::__construct($kernel, $belong_to, $belong_to_id);
    }
}

class Intraface_modules_shop_Controller_Categories_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getModel($id = 0)
    {
        if ($id == 0) {
            $id = $this->name();
        }
        return $this->context->getModel($id);
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

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFileAppender()
    {
        return new Category_Appender($this->getKernel(), 'category', $this->getModel()->getId());
    }

    function getPictures()
    {
        // @todo The fileappender should know which files are appended
        //       and know that this only takes one file.
        $module = $this->getKernel()->useShared('filehandler');
        require_once 'Intraface/shared/filehandler/AppendFile.php';

        $pictures = array();
        $append_file = new Category_Appender($this->getKernel(), 'category', $this->getModel()->getId());
        $appendix_list = $append_file->getList();
        foreach ($appendix_list as $picture) {
            $pictures[] = new Ilib_Filehandler($this->getKernel(), $picture['file_handler_id']);
        }
        return $pictures;
    }
}