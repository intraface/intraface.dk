<?php
class Intraface_modules_product_Controller_Attributegroup extends k_Component
{
    function postForm()
    {
        Intraface_Doctrine_Intranet::singleton($this->context->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $group = $gateway->findById($this->name());

        $group->name = $_POST['name'];
        $group->description = $_POST['description'];
        try {
            $group->save();
            $group->load();
            return new k_SeeOther($this->url());
        } catch(Doctrine_Validator_Exception $e) {
            $error = new Intraface_Doctrine_ErrorRender($translation);
            $error->attachErrorStack($group->getErrorStack());
        }

    }

    function renderHtmlEdit()
    {
        Intraface_Doctrine_Intranet::singleton($this->context->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $group = $gateway->findById($this->name());
        $attributes = $group->getAttributes();
        $data = array('group' => $group);

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroup-edit.tpl.php');
        return $smarty->render($this, $data);
    }

    function renderHtml()
    {

        Intraface_Doctrine_Intranet::singleton($this->context->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $group = $gateway->findById($this->name());
        $attributes = $group->getAttributes();

        $data = array('group' => $group, 'attributes' => $attributes);
        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroup.tpl.php');
        return $smarty->render($this, $data);
    }

    function t($phrase)
    {
        return $phrase;
    }
}