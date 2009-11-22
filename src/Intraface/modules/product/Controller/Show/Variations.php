<?php
class Intraface_modules_product_Controller_Show_Variations extends k_Component
{
    function map($name)
    {
        if ($name == 'select_attribute_groups') {
            // @todo check whether product has attributes
            return 'Intraface_modules_product_Controller_Show_Variations_SelectAttributeGroups';
        }
    }
    
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }
    
    function renderHtml()
    {
        $product = $this->context->getProduct();
        $groups = $product->getAttributeGroups();
        /*if(count($groups) == 0) {
            return new k_TemporaryRedirect($this->url('select_attribute_groups'));
        }*/
        
        parent::execute();
        
        $product = new Product($kernel, $_GET['id']);
        $existing_groups = array();
        $groups = $product->getAttributeGroups();
        
        
    }
}
?>