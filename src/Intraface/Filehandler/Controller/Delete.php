<?php
class Intraface_Filehandler_Controller_Delete extends k_Component
{
    function renderHtml()
    {
        $kernel = $this->registry->get('intraface:kernel');
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = new Ilib_Filehandler($kernel, $this->context->name);
        if (!$filemanager->delete()) {
            trigger_error($this->__('could not delete file'), E_USER_ERROR);
        }
        return new k_SeeOther($this->context->url('../'));
    }

}
