<?php
class Intraface_Fileimport_Controller_Index extends k_Component
{
    protected $template;
    public $values;
    public $fields;
    public $translation_page_id;
    public $mode;
    protected $fileimport;
    public $filehandler;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function postMultipart()
    {
        $shared_fileimport = $this->getKernel()->useShared('fileimport');
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $translation = $this->getKernel()->getTranslation('fileimport');

        $this->fileimport = new FileImport;

        /*
        $redirect = Intraface_Redirect::receive($this->getKernel());

        if ($redirect->get('id') == 0) {
            throw new Exception('we did not find a redirect, which is needed');
            exit;
        }
        */

        if (isset($_POST['upload_file'])) {

            $this->filehandler = new Filehandler($this->getKernel());
            $this->filehandler->createUpload();

            if ($file_id = $this->filehandler->upload->upload('userfile', 'temporary')) {
                $this->filehandler = new FileHandler($this->getKernel(), $file_id);
                if ($this->filehandler->get('id') == 0) {
                    throw new Exception('unable to load file after upload');
                }
                $parser = $this->fileimport->createParser('CSV');
                if ($this->values = $parser->parse($this->filehandler->get('file_path'), 0, 1)) {
                    if (empty($this->values) || empty($this->values[0])) {
                        $fileimport->error->set('No data was found in the file');
                    } else {
                        // This is now only for contact!
                        $this->fields = array('number', 'name', 'address', 'postcode', 'city', 'country', 'cvr', 'email', 'website', 'phone', 'ean', 'type', 'paymentcondition', 'preferred_invoice', 'openid_url');
                        $this->translation_page_id = 'contact';
                        $this->mode = 'select_fields';
                    }

                }
                $this->fileimport->error->merge($parser->error->getMessage());
            }
            $this->fileimport->error->merge($this->filehandler->error->getMessage());
        } elseif (isset($_POST['save'])) {
            $this->filehandler = new Filehandler($this->getKernel(), $_POST['file_id']);
            if ($this->filehandler->get('id') == 0) {
                throw new Exception('unable to load data file');
            } elseif (empty($_POST['fields']) || !is_array($_POST['fields'])) {
                throw new Exception('there was no fields!');
            } else {
                $parser = $this->fileimport->createParser('CSV');
                $parser->assignFieldNames($_POST['fields']);
                if (!empty($_POST['header'])) {
                    $offset = 1;
                } else {
                    $offset = 0;
                }

                if ($data = $parser->parse($this->filehandler->get('file_path'), $offset)) {
                    $_SESSION['shared_fileimport_data'] = $data;
                    $this->session()->set('fileimport_data', $data);

                    return new k_SeeOther($this->url('../', array('header' => $this->body('header'),'file_id' => $this->filehandler->get('id'), 'fields' => $this->body('fields'))));

                    /*


                    $redirect->setParameter('session_variable_name', 'shared_fileimport_data');
                    if ($url = $redirect->getRedirect('')) {
                        return new k_SeeOther($url);
                    } else {
                        throw new Exception('No redirect url was found.');
                    }
                    */
                }

            }
        }
        return $this->render();
    }

    function renderHtml()
    {
        $shared_fileimport = $this->getKernel()->useShared('fileimport');
        $shared_filehandler = $this->getKernel()->useModule('filemanager');
        $translation = $this->getKernel()->getTranslation('fileimport');

        if (!is_object($this->fileimport)) {
            $this->fileimport = new FileImport;
        }


        /*
        $redirect = Intraface_Redirect::receive($this->getKernel());

        if ($redirect->get('id') == 0) {
            throw new Exception('we did not find a redirect, which is needed');
        }
		*/

        $data = array(
            'fileimport' => $this->fileimport
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}