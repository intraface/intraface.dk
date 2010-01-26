<?php
class Intraface_Filehandler_Controller_Batchedit extends k_Component
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

    function postForm()
    {
        $kernel = $this->getKernel();
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $input = $this->body();

        foreach ($this->body('description') as $key => $value) {
            $filemanager = $gateway->getFromId($key);
            if ($filemanager->update(array(
                'description' => $input['description'][$key],
                'accessibility' => $input['accessibility'][$key]
                ))) {

                $appender = $filemanager->getKeywordAppender();
                $string_appender = new Intraface_Keyword_StringAppender($filemanager->getKeyword(), $appender);
                $string_appender->addKeywordsByString($input['keywords'][$key]);
            }
        }

        return new k_SeeOther($this->context->url(), array('use_stored' => 'true'));
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $gateway = new Ilib_Filehandler_Gateway($this->getKernel());
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        if (!$this->query('use_stored')) {
            throw new Exception('you cannot batch edit files with no save results');
        }

        $gateway->getDBQuery()->storeResult('use_stored', 'filemanager', 'toplevel');

        $files = $gateway->getList();

        $this->document->setTitle('files');

        $data = array('gateway' => $gateway, 'files' => $files, 'kernel' => $kernel);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/batchedit');
        return $tpl->render($this, $data);
    }
}