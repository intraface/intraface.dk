<?php
class Intraface_Filehandler_Controller_Undelete extends k_Component
{
    function renderHtml()
    {
        $kernel = $this->registry->get('intraface:kernel');
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $filemanager = new Ilib_Filehandler_Manager($kernel, $this->context->name);
        if (!$filemanager->undelete()) {
            trigger_error($translation->get('could not delete file'), E_USER_ERROR);
        }
        return new k_SeeOther($this->context->url('../'));
    }

}
