<?php
class Intraface_Filehandler_Controller_UploadScript extends k_Component
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
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");

        $data = array('kernel' => $kernel);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/uploadscript');

        return new k_HttpResponse(200, $tpl->render($this, $data));
    }

    function postMultipart()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");

        $data = array('kernel' => $kernel, 'filemanager' => new Ilib_Filehandler($kernel));

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/uploadscript-post');

        return new k_HttpResponse(200, $tpl->render($this, $data));
    }
}