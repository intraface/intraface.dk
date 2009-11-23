<?php
class Intraface_Filehandler_Controller_Upload extends k_Component
{
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();

        $redirect = Ilib_Redirect::receive($kernel->getSessionId(), $this->mdb2);

        $filemanager = new Ilib_Filehandler($kernel);

        $this->document->setTitle('Upload file');

        $data = array('filemanager' => $filemanager, 'redirect' => $redirect);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/upload.tpl.php');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module('filemanager');

        $redirect = Ilib_Redirect::receive($kernel->getSessionId(), $this->mdb2);

        $filemanager = new Ilib_Filehandler($kernel);

        $filemanager->getUploader()->setSetting('file_accessibility', $this->body('accessibility'));
        $filemanager->getUploader()->setSetting('max_file_size', '10000000');
        $filemanager->getUploader()->setSetting('add_keyword', $this->body('keyword'));
        if($id = $filemanager->getUploader()->upload('userfile')) {
            $location = $redirect->getRedirect($this->context->url($id));
            return new k_SeeOther($location);
        }

        return $this->render();
    }

    function t($phrase)
    {
        return $phrase;
    }
}