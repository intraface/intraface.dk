<?php
/**
 * @todo If user changes e-mail we need to do something special
 *       if we will continue using this - so I have disabled
 *       e-mail change
 *
 *
 * @category
 * @package
 * @author     lsolesen
 * @copyright
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version
 *
 */
class Intraface_modules_controlpanel_Controller_User extends k_Component
{
    protected $user;
    protected $user_gateway;
    protected $template;

    function __construct(Intraface_UserGateway $user_gateway, k_TemplateFactory $template)
    {
        $this->user_gateway = $user_gateway;
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'preferences') {
            return 'Intraface_modules_controlpanel_Controller_UserPreferences';
        } elseif ($name == 'changepassword') {
            return 'Intraface_modules_controlpanel_Controller_ChangePassword';
        }
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/user');
        return $smarty->render($this, $this->getValues());
    }

    function renderHtmlEdit()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/useredit');
        return $smarty->render($this, $this->getValues());
    }

    function putForm()
    {
        $value = $_POST;
        $address_value = $_POST;
        $address_value['name'] = $_POST['address_name'];
        $address_value['email'] = $_POST['address_email'];
        
        $old_email = $this->getUser()->get('email');

        // @todo hvis man ændrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
        // på den gamle e-mail

        //$this->getUser()->setActiveIntranetId($this->getUser()->getActiveIntranet());
        

        if ($this->getUser()->update($value)) {
            if($this->getUser()->get('email') != $old_email) {
                $user = new Intraface_AuthenticatedUser($this->getUser()->get('email'), $this->language());
                $this->session()->set('intraface_identity', $user);
            }
            
            if ($this->getUser()->getAddress()->save($address_value)) {
                return new k_SeeOther($this->url(null));
            }
        }

        return $this->render();
    }

    function getValues()
    {
         if ($this->body()) {
            $value = $this->body();
            $address_value['name'] = $this->body('address_name');
            $address_value['email'] = $this->body('address_email');
            return array('value' => $value, 'address_value' => $address_value);
        }
        $user = $this->getUser();
        $value = $user->get();
        $address_value = $user->getAddress()->get();
        return array('value' => $value, 'address_value' => $address_value);
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getUser()
    {
        if (is_object($this->user)) {
            return $this->user;
        }
        $user = $this->user_gateway->findByUsername($this->identity()->user());
        require_once 'Intraface/modules/administration/UserAdministration.php';
        return $this->user = new UserAdministration($this->getKernel(), $user->getId());
    }
}