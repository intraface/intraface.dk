<?php
class Intraface_Filehandler_Controller_Delete extends k_Component
{
    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = new Ilib_Filehandler($kernel, $this->context->name());
        if (!$filemanager->delete()) {
            throw new Exception('Could not delete file');
        }
        return new k_SeeOther($this->context->url('../'));
    }

}
