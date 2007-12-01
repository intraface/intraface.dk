<?php
class Intraface_Tools_Controller_Login extends k_Controller
{
    private $form;

    function __construct(k_iContext $parent, $name = "")
    {
        parent::__construct($parent, $name);

        $descriptors = array();
        $descriptors[] = Array('name' => 'username', 'filters' => array('trim', 'strtolower'));
        $descriptors[] = Array('name' => 'password', 'filters' => array('trim', 'strtolower'));
        $this->form = new k_FormBehaviour($this, dirname(__FILE__) . '/../tpl/form.tpl.php');
        $this->form->descriptors = $descriptors;
    }

    function execute()
    {
        return $this->form->execute();
    }

    function validate($values)
    {
        return TRUE;
    }

    function validHandler($values)
    {
        $user = $this->registry->get('user');
        if (!$user->login($values['username'], $values['password'])) {
            throw new Exception('could not login');
        }
        // It would be proper REST to reply with 201, but browsers doesn't understand that
        throw new k_http_Redirect($this->context->url());
    }

}