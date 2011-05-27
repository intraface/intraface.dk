<?php
class Intraface_Filehandler_Controller_UploadScript extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");
        $filemanager = new Intraface_modules_filemanager_FileManager($kernel);

        $data = array(
            'kernel' => $kernel,
            'filemanager' => $filemanager);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/uploadscript');

        return new k_HttpResponse(200, $tpl->render($this, $data));
    }

    function postMultipart()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");
        $filemanager = new Intraface_modules_filemanager_FileManager($kernel);
        $filemanager->createUpload();
        $filemanager->upload->setSetting('file_accessibility', 'public');
        $filemanager->upload->setSetting('max_file_size', '10000000');

        $data = array(
            'kernel' => $kernel,
            'filemanager' => $filemanager);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/uploadscript-post');

        return new k_HtmlResponse($tpl->render($this, $data));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}