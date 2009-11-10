<?php
class Intraface_modules_controlpanel_Controller_User extends k_Component
{
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

    function postForm()
    {
        require_once 'Intraface/modules/administration/UserAdministration.php';
        $user = new UserAdministration($this->getKernel(), $this->getKernel()->user->get('id'));

        $value = $_POST;
        $address_value = $_POST;
        $address_value['name'] = $_POST['address_name'];
        $address_value['email'] = $_POST['address_email'];

        // @todo hvis man ændrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
        // på den gamle e-mail

        if ($user->update($_POST)) {
            if ($user->getAddress()->validate($address_value) && $user->getAddress()->save($address_value)) {
                return new k_SeeOther($this->url(null));
            }
        }

        return $this->render();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getValues()
    {
        $translation = $this->getKernel()->getTranslation('controlpanel');

        $user = $this->getKernel()->user;
        $value = $user->get();
        $address_value = $user->getAddress()->get();
        return array_merge($value, $address_value);
    }

    function getUser()
    {
        return $this->getKernel()->user;
    }
}