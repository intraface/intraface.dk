<?php
class Intraface_modules_contact_Controller_Import extends k_Component
{
    protected $registry;
    protected $msg;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function postForm()
    {
        throw new Exception('not implemented');
        /*
        $data = $_SESSION['shared_fileimport_data'];

        if (!is_array($data) || count($data) == 0) {
            trigger_error('This is not a valid dataset!', E_USER_ERROR);
            exit;
        }

        $errors = array();
        $e = 0;
        $success = 0;

        foreach ($data AS $line => $contact) {

            $contact_object = new Contact($kernel);

            if ($contact_object->save($contact)) {

                $appender = $contact_object->getKeywordAppender();
                $string_appender = new Intraface_Keyword_StringAppender($contact_object->getKeywords(), $appender);

                $string_appender->addKeywordsByString($_POST['keyword']);
                $success++;
            }
            else {
                $errors[$e]['line'] = $line+1; // line starts at 0
                $errors[$e]['name'] = $contact['name'];
                $errors[$e]['email'] = $contact['email'];
                $errors[$e]['error'] = $contact_object->error;
                $e++;
            }
        }

        unset($_SESSION['shared_fileimport_data']);
    	*/
    }

    function renderHtml()
    {
        return '<h1>Contact import</h1><p>Not implemented yet. Write to support@intraface.dk if you need it. <a href="'.$this->url('../').'">Go back</a>.</p>';

/*
    $redirect = Intraface_Redirect::returns($kernel);
    if ($redirect->getId('id') == 0) {
        trigger_error('we did not recive a redirect', E_USER_ERROR);
        exit;
    }

    if ($redirect->getParameter('session_variable_name') != 'shared_fileimport_data') {
        trigger_error('the session variable name must have been changed as it is not the same anymore: "'.$redirect->getParameter('session_variable_name').'"', E_USER_ERROR);
        exit;
    }

    $data = $_SESSION['shared_fileimport_data'];
        $smarty = new k_Template(dirname(__FILE__) . '/templates/sendemail.tpl.php');
        return $smarty->render($this);
*/

    }
}

