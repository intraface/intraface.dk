<?php
class Intraface_Filehandler_Controller_Edit extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());
        $filemanager = $gateway->getFromId(intval($this->context->name));
        $values = $filemanager->get();
        $this->document->setTitle('edit file');

        $data = array('filemanager' => $filemanager,
                      'values' => $values);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/edit.tpl.php');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());
        $filemanager = $gateway->getFromId(intval($this->context->name));

        $uploader = $filemanager->getUploader();
        $uploader->setSetting('max_file_size', '1000000');
        if ($uploader->isUploadFile('replace_file')) { //
            $upload_result = $uploader->upload('replace_file');
        } elseif('' != ($message = $uploader->getUploadFileErrorMessage('replace_file'))) {
            $upload_result = false;
            $filemanager->error->set($message);
        } else {
            $upload_result = true;
        }

        if ($filemanager->update($this->body()) && $upload_result) {
            return new k_SeeOther($this->context->url());
        }

        $data = array('filemanager' => $filemanager,
                      'values' => $this->POST->getArrayCopy());


        $tpl = new k_Template(dirname(__FILE__) . '/../templates/edit.tpl.php');
        return $tpl->render($this, $data);
    }

    function t($phrase)
    {
        return $phrase;
    }
}