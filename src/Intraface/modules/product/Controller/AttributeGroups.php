<?php
class Intraface_modules_product_Controller_AttributeGroups extends k_Component
{
    private $error;
    
    function getKernel()
    {
        return $this->context->getKernel();
    }
    
    function getError()
    {
        if(!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getKernel()->getTranslation('product'));
        }

        return $this->error;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $groups = $gateway->findAll();

        $data = array('groups' => $groups);

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroups.tpl.php');
        return $smarty->render($this, $data);

    }

    function map($name)
    {
        return 'Intraface_modules_product_Controller_AttributeGroups_Show';
    }

    function postForm()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        if($this->body('save') != '') {
            $group = new Intraface_modules_product_Attribute_Group;
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            try {
                $group->save();
                $group->load();
                return new k_SeeOther($this->url($group->getId()));
            } catch(Doctrine_Validator_Exception $e) {
                $this->getError()->attachErrorStack($group->getErrorStack());
            }
        }

        return $this->render();
    }

    function renderHtmlCreate()
    {
        $data = array();
        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroup-edit.tpl.php');
        return $smarty->render($this, $data);

    }
}