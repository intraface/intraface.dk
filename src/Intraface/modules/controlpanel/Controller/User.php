<?php
class Intraface_modules_controlpanel_Controller_User extends k_Component
{
    protected $user;
    protected $user_gateway;

    function __construct(Intraface_UserGateway $user_gateway)
    {
        $this->user_gateway = $user_gateway;
    }

    function map($name)
    {
        if ($name == 'preferences') {
            return 'Intraface_modules_controlpanel_Controller_UserPreferences';
        } elseif ($name == 'changepassword') {
            return 'Intraface_modules_controlpanel_Controller_ChangePassword';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/user.tpl.php');
        return $smarty->render($this);
    }

    function renderHtmlEdit()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/useredit.tpl.php');
        return $smarty->render($this);
    }

    function getUser()
    {
        if (is_object($this->user)) {
            return $this->user;
        }
        return ($this->user = $this->user_gateway->findByUsername($this->identity()->user()));

    }

    function putForm()
    {
        $value = $_POST;
        $address_value = $_POST;
        $address_value['name'] = $_POST['address_name'];
        $address_value['email'] = $_POST['address_email'];

        // @todo hvis man ændrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
        // på den gamle e-mail
        if ($this->getUser()->update($value)) {
            if ($this->getUser()->getAddress()->validate($address_value) && $this->getUser()->getAddress()->save($address_value)) {
                return new k_SeeOther($this->url(null));
            }
        }

        return $this->render();
    }

    function getValues()
    {
        if ($this->body()) {
            $values = $this->body();
            $values['name'] = $this->body('address_name');
            $values['email'] = $this->body('address_email');
            return $values;
        }
        $user = $this->getUser();
        $value = $user->get();
        $address_value = $user->getAddress()->get();
        $address_value['address_name'] = $address_value['name'];
        $address_value['address_email'] = $address_value['email'];

        return array_merge($value, $address_value);
    }
}