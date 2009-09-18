<?php
class Intraface_modules_intranetmaintenance_Controller_User_Edit extends k_Component
{
    protected $registry;
    protected $user;

    function renderHtml()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/user/edit.tpl.php');
        return $smarty->render($this);
    }

    function getUser()
    {
        if (is_object($this->user)) {
            return $this->user;
        }
        return $this->user = new UserMaintenance($this->context->name());
    }

    function getIntranetmaintenance()
    {
        if (is_object($this->intranetmaintenance)) {
            return $this->intranetmaintenance;
        }
        return $this->intranetmaintenance = new IntranetMaintenance();
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getIntranet()
    {
        return $this->getKernel()->intranet;
    }

    function postForm()
    {
        $module = $this->getKernel()->module("intranetmaintenance");

        $user = new UserMaintenance(intval($this->context->name()));

        if (isset($_POST["intranet_id"]) && intval($_POST["intranet_id"]) != 0) {
            $intranet = new Intraface_Intranet($_POST["intranet_id"]);
            $intranet_id = $intranet->get("id");
            $address_value = $_POST;
            $address_value["name"] = $_POST["address_name"];
        } else {
            $intranet_id = 0;
            $address_value = array();
        }

        $value = $_POST;

        if ($user->update($_POST)) {
            if (isset($intranet)) {
                $user->setIntranetAccess($intranet->get('id'));
                $user->setIntranetId($intranet->get('id'));
                $user->getAddress()->save($address_value);
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../', array('intranet_id' => $intranet->get("id"))));
                } else {
                    return new k_SeeOther($this->url('../' . $user->getId(), array('intranet_id' => $intranet->get("id"))));
                }
            } else {
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../'));
                } else {
                    return new k_SeeOther($this->url('../' . $user->getId()));
                }

            }
        }
        return $this->render();
    }

    function getValues()
    {
        if (is_numeric($this->context->name())) {
            $user = new UserMaintenance(intval($this->context->name()));
            $value = $user->get();

            if (isset($_REQUEST['intranet_id'])) {
                $intranet_id = intval($_REQUEST["intranet_id"]);
                $user->setIntranetId($intranet_id);
                $address_value = $user->getAddress()->get();
            } else {
                $intranet_id = 0;
                $address_value = array();
            }
        } else {
            $user = new UserMaintenance();
            $intranet_id = intval($_REQUEST["intranet_id"]);
            $value = array();
            $address_value = array();
        }
        return array_merge($address_value, $value);
    }
}
