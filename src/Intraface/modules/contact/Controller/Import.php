<?php
class Intraface_modules_contact_Controller_Import extends k_Component
{
    protected $msg;
    public $errors;
    public $success;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_Fileimport_Controller_Index';
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $data = $this->session()->get('fileimport_data');

        if (!is_array($data) || count($data) == 0) {
            throw new Exception('This is not a valid dataset!');
        }

        $this->errors = array();
        $e = 0;
        $this->success = 0;

        foreach ($data AS $line => $contact) {

            $contact_object = new Contact($this->getKernel());

            if ($contact_object->save($contact)) {

                $appender = $contact_object->getKeywordAppender();
                $string_appender = new Intraface_Keyword_StringAppender($contact_object->getKeywords(), $appender);

                $string_appender->addKeywordsByString($_POST['keyword']);
                $this->success++;
            } else {
                $this->errors[$e]['line'] = $line+1; // line starts at 0
                $this->errors[$e]['name'] = $contact['name'];
                $this->errors[$e]['email'] = $contact['email'];
                $this->errors[$e]['error'] = $contact_object->error;
                $e++;
            }
        }

        //unset($_SESSION['shared_fileimport_data']);
        $data = $this->session()->set('fileimport_data', null);

        if (empty($this->errors)) {
            return new k_SeeOther($this->url('../', array('flare' => $this->success . ' contacts imported successfully')));
        }

        return $this->render();
    }

    function renderHtml()
    {
        /*
        $redirect = Intraface_Redirect::returns($this->getKernel());
        if ($redirect->getId('id') == 0) {
            throw new Exception('We did not recive a redirect');
        }
        */
        //$shared_fileimport = $this->getKernel()->useShared('fileimport');
        //$shared_filehandler = $this->getKernel()->useShared('filehandler');
        $translation = $this->getKernel()->getTranslation('contact');
        /*
        $fileimport = new FileImport;

            $filehandler = new Filehandler($this->getKernel(), $this->query('file_id'));
            if ($filehandler->get('id') == 0) {
                throw new Exception('unable to load data file');
            } elseif (!is_array($this->query('fields'))) {
                throw new Exception('there was no fields!');
            } else {
                $parser = $fileimport->createParser('CSV');
                $parser->assignFieldNames($this->query('fields'));
                if (!$this->query('header')) {
                    $offset = 1;
                } else {
                    $offset = 0;
                }

                if (!$data = $parser->parse($filehandler->get('file_path'), $offset)) {
                    throw new Exception('Could not parse the file');
                }
            }
            */
        /*
        if ($redirect->getParameter('session_variable_name') != 'shared_fileimport_data') {
            throw new Exception('the session variable name must have been changed as it is not the same anymore: "'.$redirect->getParameter('session_variable_name').'"');
        }
        */

        //$data = array('translation' => $translation);

        //$data = $_SESSION['shared_fileimport_data'];
        $data = array('data' => $this->session()->get('fileimport_data'));

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/import');
        return $smarty->render($this, $data);
    }
}