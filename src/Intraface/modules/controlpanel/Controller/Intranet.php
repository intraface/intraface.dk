<?php
class Intraface_modules_controlpanel_Controller_Intranet extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/intranet.tpl.php');
        return $smarty->render($this);
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getValues()
    {
        $this->getKernel()->useShared('filehandler');

        $translation = $this->getKernel()->getTranslation('controlpanel');


        $values = $this->getKernel()->intranet->get();
        $address = $this->getKernel()->intranet->address->get();

        return array_merge($values, $address);
    }
}