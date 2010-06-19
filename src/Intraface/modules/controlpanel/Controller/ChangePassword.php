<?php
class Intraface_modules_controlpanel_Controller_ChangePassword extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/changepassword');
        return $smarty->render($this);
    }

    function putForm()
    {
    	if ($this->getUser()->updatePassword($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password'])) {
    		return new k_SeeOther($this->url('../'));
    	}
    	return $this->render();
    }

    function getValues()
    {
        $value = $this->getUser()->get();
        $address_value = $this->getUser()->getAddress()->get();
        return array_merge($value, $address_value);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getUser()
    {
        return new Intraface_User($this->getKernel()->user->get('id'));
    }
}