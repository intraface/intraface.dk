<?php
class Intraface_modules_controlpanel_Controller_ChangePassword extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/changepassword');
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

    function putForm()
    {
    	$user = new Intraface_User($this->getKernel()->user->get('id'));

    	if ($user->updatePassword($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password'])) {
    		return new k_SeeOther($this->url('../'));
    	}
    	return render();
    }

    function getUser()
    {
        return  new Intraface_User($this->getKernel()->user->get('id'));
    }
}