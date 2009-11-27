<?php
class Intraface_modules_contact_Controller_Import extends k_Component
{
    protected $msg;
    public $errors;
    public $success;

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
        $data = $_SESSION['shared_fileimport_data'];

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

        unset($_SESSION['shared_fileimport_data']);

        if (empty($this->errors)) {
            return new k_SeeOther($this->url('../', array('flare' => $this->success . ' contacts imported successfully')));
        }

        return $this->render();
    }

    function renderHtml()
    {
        $redirect = Intraface_Redirect::returns($this->getKernel());
        if ($redirect->getId('id') == 0) {
            throw new Exception('We did not recive a redirect');
        }

        $translation = $this->getKernel()->getTranslation('contact');

        if ($redirect->getParameter('session_variable_name') != 'shared_fileimport_data') {
            throw new Exception('the session variable name must have been changed as it is not the same anymore: "'.$redirect->getParameter('session_variable_name').'"');
        }

        $data = $_SESSION['shared_fileimport_data'];
        $smarty = new k_Template(dirname(__FILE__) . '/templates/import.tpl.php');
        return $smarty->render($this, array('translation' => $translation));
    }
}