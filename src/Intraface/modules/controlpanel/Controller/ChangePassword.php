<?php
class Intraface_modules_controlpanel_Controller_ChangePassword extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/changepassword.tpl.php');
        return $smarty->render($this);
    }

    function getValues()
    {
        $translation = $this->getKernel()->getTranslation('controlpanel');

        $user = new Intraface_User($this->getKernel()->user->get('id'));
        $value = $user->get();
        $address_value = $user->getAddress()->get();
        return array_merge($value, $address_value);
    }

    function renderForm()
    {
    	$user = new Intraface_User($this->getKernel()->user->get('id'));

    	if ($user->updatePassword($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password'])) {
    		return k_SeeOther($this->url('../'));
    	}
    	return renderHtml();
    }

    function getUser()
    {
        return  new Intraface_User($this->getKernel()->user->get('id'));
    }
}