<?php
class Intraface_Filehandler_Controller_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function renderHtml()
    {
        $filemanager = $this->getObject();

        if ($filemanager->getId() == 0) {
            throw new k_PageNotFound();
        }

        $this->document->setTitle('file') . ': ' . $filemanager->get('file_name');

        $data = array('filemanager' => $filemanager,
                      'kernel'      => $this->getKernel());

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/show');
        return $tpl->render($this, $data);
    }

    function getObject()
    {
    	$gateway = new Ilib_Filehandler_Gateway($this->getKernel());
        return $gateway->getFromId($this->name());
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_Filehandler_Controller_Edit';
        } elseif ($name == 'crop') {
            return 'Intraface_Filehandler_Controller_Crop';
        } elseif ($name == 'undelete') {
            return 'Intraface_Filehandler_Controller_Undelete';
        } elseif ($name == 'delete') {
            return 'Intraface_Filehandler_Controller_Delete';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }
}