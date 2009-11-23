<?php
class Intraface_Filehandler_Controller_UploadScript extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");

        $data = array('kernel' => $kernel);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/uploadscript.tpl.php');

        return new k_HttpResponse(200, $tpl->render($this, $data));
    }

    function postMultipart()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module("filemanager");

        $data = array('kernel' => $kernel, 'filemanager' => new Ilib_Filehandler($kernel));

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/uploadscript-post.tpl.php');

        return new k_HttpResponse(200, $tpl->render($this, $data));
    }

    function t($phrase)
    {
        return $phrase;
    }
}