<?php
class Intraface_modules_intranetmaintenance_Controller_Intranet_Key extends k_Component
{
    protected $intranetmaintenance;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/intranet/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');
        $intranet = new IntranetMaintenance(intval($_POST["id"]));

        $value = $_POST;
        $address_value = $_POST;
        $address_value["name"] = $_POST["address_name"];

        if ($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $this->getKernel()->intranet->get('id'))) {
            if ($intranet->address->save($address_value)) {
                return new k_SeeOther($this->url('../'));
            }
        }

        return $this->render();
    }

    function getValues()
    {
        if (is_numeric($this->context->name())) {
            $intranet = new IntranetMaintenance((int)$this->context->name());
            $value = $intranet->get();
            $address_value = $intranet->address->get();
        } else {
            $intranet = new IntranetMaintenance();
            $value = array();
            $address_value = array();
        }

        $array = array_merge($value, $address_value);
        return $array;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getIntranet()
    {
        return $this->context->getIntranet();
    }
}
