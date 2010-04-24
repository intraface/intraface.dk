<?php
class Intraface_Filehandler_Controller_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getFile()->getId() == 0 AND !$this->query('restore')) {
            throw new k_PageNotFound();
        }

        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'crop') {
            return 'Intraface_Filehandler_Controller_Crop';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle('file') . ': ' . $this->getFile()->get('file_name');

        $data = array('filemanager' => $this->getFile(),
                      'kernel'      => $this->getKernel());

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/show');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        if ($this->body()) {
            $values = $this->body();
        } else {
            $values = $this->getFile()->get();
        }
        $this->document->setTitle('Edit file');

        $data = array('filemanager' => $this->getFile(),
                      'values' => $values);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/edit');
        return $tpl->render($this, $data);
    }

    function postMultipart()
    {
        $filemanager = $this->getObject();

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
            return new k_SeeOther($this->url());
        }

        return $this->render();
    }

    function renderHtmlDelete()
    {
        $module = $this->getKernel()->module('filemanager');

        if (!$this->getFile()->delete()) {
            throw new Exception('Could not delete file');
        }
        return new k_SeeOther($this->context->url(null, array('flare' => 'File has been deleted')));
    }

    function renderHtmlRestore()
    {
        $module = $this->getKernel()->module('filemanager');

        $filemanager = $this->getObject();
        if (!$filemanager->undelete()) {
            throw new Exception('Could not undelete file');
        }
        return new k_SeeOther($this->url(null, array('flare' => 'File has been restored')));
    }

    /**
     * @see Keywords
     *
     * @return object
     */
    function getModel()
    {
        return $this->getObject();
    }

    function getObject()
    {
        return $this->context->getGateway()->getFromId($this->name());
    }

    function getFile()
    {
        return $this->getObject();
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }
}