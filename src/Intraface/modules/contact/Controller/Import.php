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

    function renderHtml()
    {
        $data = array('data' => $this->session()->get('fileimport_data'));

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/import');
        return $smarty->render($this, $data);
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

        foreach ($data as $line => $contact) {
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

        $data = $this->session()->set('fileimport_data', null);

        if (empty($this->errors)) {
            return new k_SeeOther($this->url('../', array('flare' => $this->success . ' contacts imported successfully')));
        }

        return $this->render();
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
