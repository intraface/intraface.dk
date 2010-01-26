<?php
class Intraface_Filehandler_Controller_UploadMultiple extends k_Component
{
    private $mdb2;
    protected $template;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
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

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/uploadmultiple');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $kernel = $this->getKernel();
        $module = $kernel->module('filemanager');
        $translation = $kernel->getTranslation('filemanager');

        $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
        $redirect = Ilib_Redirect::factory($kernel->getSessionId(), $this->mdb2, 'receive', $options);

        if (!empty($_POST['addfile'])) {
            foreach ($_POST['addfile'] as $key => $value) {
                $gateway = new Ilib_Filehandler_Gateway($kernel);
                $filemanager = $gateway->getFromId($value);
                $appender = $filemanager->getKeywordAppender();
                $string_appender = new Intraface_Keyword_StringAppender(new Keyword($filemanager), $appender);
                $string_appender->addKeywordsByString($_POST['keywords']);

                $filemanager->update(array('accessibility' => $_POST['accessibility']));

                if ($filemanager->moveFromTemporary()) {
                    // $msg = 'Filerne er uploadet. <a href="'.$redirect->getRedirect('/modules/filemanager/').'">�bn filarkivet</a>.';
                } else {
                    throw new Exception('Could not move the files from temporary to uploaded.');
                }
            }
        }
        $location = $redirect->getRedirect($this->context->url());
        return new k_SeeOther($location);
    }
}