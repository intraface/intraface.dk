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

        $options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
        $redirect = Ilib_Redirect::factory($kernel->getSessionId(), $this->mdb2, 'receive', $options);

        if (!empty($_POST['addfile'])) {
            foreach ($_POST['addfile'] as $key => $value) {
                $filemanager = $this->context->getGateway()->getFromId($value);

                $appender = new Intraface_Keyword_Appender($filemanager);

                $string_appender = new Intraface_Keyword_StringAppender(new Keyword($filemanager), $appender);
                $string_appender->addKeywordsByString($this->body('keywords'));

                $filemanager->update(array('accessibility' => $_POST['accessibility']));

                if ($filemanager->moveFromTemporary()) {
                    // files has been uploaded
                } else {
                    throw new Exception('Could not move the files from temporary to uploaded.');
                }
            }
        }
        $location = $redirect->getRedirect($this->context->url());
        return new k_SeeOther($location);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
