<?php
/**
 * @author      Sune Jensen <sune@intraface.dk>
 * @version     1.0
 *
 */
class Intraface_modules_email_Controller_Settings extends k_Component
{
    protected $error;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }
    
    function renderHtml()
    {
        $this->context->getKernel()->useModule('email');

        $smarty = $this->template->create('Intraface/modules/email/Controller/templates/settings');
        return $smarty->render($this, array('values' => $this->getValues(), 'error' => $this->getError()));
    }

    function getError()
    {
        if (is_object($this->error)) {
            return $this->error;
        }

        return $this->error = new Intraface_Error();
    }

    function postForm()
    {
        $this->context->getKernel()->useModule('email');

        if (!empty($_POST)) {
            $error = $this->getError();
            $validator = new Intraface_Validator($error);

            if ($_POST['signature_type'] == 2) {
                $validator->isString($_POST['custom_signature'], 'Error in custom signature');
            } else {
                $validator->isString($_POST['custom_signature'], 'Error in custom signature', '', 'allow_empty');
            }

            if (!$error->isError()) {
                $this->context->getKernel()->getSetting()->set('user', 'email.signature_type', $_POST['signature_type']);
                $this->context->getKernel()->getSetting()->set('user', 'email.custom_signature', $_POST['custom_signature']);
                return new k_SeeOther($this->url('../'));
            }
        }

        return $this->render();
    }

    function getValues()
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        // find settings frem
        $values['signature_type'] = $this->context->getKernel()->getSetting()->get('user', 'email.signature_type');
        $values['custom_signature'] = $this->context->getKernel()->getSetting()->get('user', 'email.custom_signature');
        
        return $values;
    }
}
