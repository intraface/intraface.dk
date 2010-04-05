<?php
class Intraface_Filehandler_Controller_Upload extends k_Component
{
    protected $mdb2;
    protected $template;
    protected $filehandler;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();

        $redirect = Ilib_Redirect::receive($kernel->getSessionId(), $this->mdb2);

        $this->document->setTitle('Upload file');

        $data = array('filemanager' => $this->getFilehandler(), 'redirect' => $redirect);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/upload');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module('filemanager');

        $redirect = Ilib_Redirect::receive($kernel->getSessionId(), $this->mdb2);

        $this->getFilehandler()->getUploader()->setSetting('file_accessibility', $this->body('accessibility'));
        $this->getFilehandler()->getUploader()->setSetting('max_file_size', '10000000');
        $this->getFilehandler()->getUploader()->setSetting('add_keyword', $this->body('keyword'));
        if ($id = $this->getFilehandler()->getUploader()->upload('userfile')) {
            $location = $redirect->getRedirect($this->context->url($id));
            return new k_SeeOther($location);
        }

        return $this->render();
    }

    function getFilehandler()
    {
        if (is_object($this->filehandler)) {
            return $this->filehandler;
        }
        return $this->filehandler = new Ilib_Filehandler($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}