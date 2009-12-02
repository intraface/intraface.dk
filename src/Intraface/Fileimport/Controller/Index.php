<?php
class Intraface_Fileimport_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function postMultipart()
    {
        $shared_fileimport = $this->getKernel()->useShared('fileimport');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $translation = $this->getKernel()->getTranslation('fileimport');

        $fileimport = new FileImport;

        $redirect = Intraface_Redirect::receive($this->getKernel());

        if ($redirect->get('id') == 0) {
            trigger_error('we did not find a redirect, which is needed', E_USER_ERROR);
            exit;
        }

        if (isset($_POST['upload_file'])) {


            $filehandler = new Filehandler($this->getKernel());
            $filehandler->createUpload();

            if ($file_id = $filehandler->upload->upload('userfile', 'temporary')) {
                $filehandler = new FileHandler($this->getKernel(), $file_id);
                if ($filehandler->get('id') == 0) {
                    trigger_error('unable to load file after upload', E_USER_ERROR);
                    exit;
                }
                $parser = $fileimport->createParser('CSV');
                if ($values = $parser->parse($filehandler->get('file_path'), 0, 1)) {
                    if (empty($values) || empty($values[0])) {
                        $fileimport->error->set('there was found no data in the file');
                    }
                    else {
                        // This is now only for contact!
                        $fields = array('number', 'name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean', 'type', 'paymentcondition', 'preferred_invoice', 'openid_url');
                        $translation_page_id = 'contact';
                        $mode = 'select_fields';
                    }

                }
                $fileimport->error->merge($parser->error->getMessage());
            }
            $fileimport->error->merge($filehandler->error->getMessage());
        } elseif (isset($_POST['save'])) {
            $filehandler = new Filehandler($this->getKernel(), $_POST['file_id']);
            if ($filehandler->get('id') == 0) {
                trigger_error('unable to load data file', E_USER_ERROR);
                exit;
            } elseif (empty($_POST['fields']) || !is_array($_POST['fields'])) {
                trigger_error('there was no fields!', E_USER_ERROR);
                exit;
            } else {
                $parser = $fileimport->createParser('CSV');
                $parser->assignFieldNames($_POST['fields']);
                if (!empty($_POST['header'])) {
                    $offset = 1;
                }
                else {
                    $offset = 0;
                }

                if ($data = $parser->parse($filehandler->get('file_path'), $offset)) {

                    //
                    $_SESSION['shared_fileimport_data'] = $data;

                    $redirect->setParameter('session_variable_name', 'shared_fileimport_data');
                    if ($url = $redirect->getRedirect('')) {
                        return new k_SeeOther($url);
                    } else {
                        trigger_error('No redirect url was found.');
                        exit;
                    }
                }

            }
        }
        return $this->render();
    }

    function renderHtml()
    {
        $shared_fileimport = $this->getKernel()->useShared('fileimport');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $translation = $this->getKernel()->getTranslation('fileimport');

        $fileimport = new FileImport;

        $redirect = Intraface_Redirect::receive($this->getKernel());

        if ($redirect->get('id') == 0) {
            throw new Exception('we did not find a redirect, which is needed');
        }

        $data = array(
            'fileimport' => $fileimport
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}