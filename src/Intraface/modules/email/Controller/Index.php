<?php
class Intraface_modules_email_Controller_Index extends k_Component
{
    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_email_Controller_Email';
        }
    }

    function renderHtml()
    {
        $this->getKernel()->module('email');
        $contact_module = $this->getKernel()->useModule('contact');
        $email_shared = $this->getKernel()->useShared('email');
        $translation = $this->getKernel()->getTranslation('email');

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $email = new Email($this->getKernel(), $_GET['delete']);
            if (!$email->delete()) {
                throw new Exception(__('could not delete e-mail', 'email'));
            }
        }

        $email_object = new Email($this->getKernel());
        $email_object->getDBQuery()->useCharacter();
        $email_object->getDBQuery()->defineCharacter('character', 'email.subject');
        $email_object->getDBQuery()->usePaging('paging');
        //$email->dbquery->storeResult('use_stored', 'emails', 'toplevel');

        $emails = $email_object->getList();
        $queue = $email_object->countQueue();

        $data = array(
            'queue' => $queue,
            'emails' => $emails,
            'email_object' => $email_object,
            'contact_module' => $contact_module,
        	'email_shared' => $email_shared
        );

        $tpl = new k_Template(dirname(__FILE__) . '/templates/index.tpl.php');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}