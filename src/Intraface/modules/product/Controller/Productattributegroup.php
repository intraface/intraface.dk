<?php
class Intraface_modules_product_Controller_Productattributegroup extends k_Component
{
    function postForm()
    {
        $product = new Product($this->getKernel(), $this->context->name());

        if (!empty($_POST['select'])) {

            $existing_groups = array();
            $new_groups = array();
            foreach ($product->getAttributeGroups() AS $group) $existing_groups[] = $group['id'];

            if (count($existing_groups) > 0) {
                try {
                    $variations = $product->getVariations();
                    if ($variations->count() > 0) {
                        $error = new Intraface_Error;
                        $error->set('You cannot change the attached attribute groups when variations has been created');
                    }
                } catch (Intraface_Gateway_Exception $e) {

                }
            }

            if (!isset($error) || $error->isError() == 0) {
                if (isset($_POST['selected']) && is_array($_POST['selected'])) {
                    $new_groups = $_POST['selected'];
                }

                foreach (array_diff($existing_groups, $new_groups) AS $id) {
                    $product->removeAttributeGroup($id);
                }

                foreach ($new_groups AS $id) {
                    $product->setAttributeGroup($id);
                }

                $existing_groups = $new_groups;

                header('location: product_variations_edit.php?id='.$product->getId());
            }
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

    function getProduct()
    {

    	Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        return new Product($this->getKernel(), $this->context->name());
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');

        $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();
        $product = $this->getProduct();

        $existing_groups = array();
        foreach ($product->getAttributeGroups() AS $group) {
            $existing_groups[] = $group['id'];
        }

        if (count($existing_groups) > 0) {
            try {
                $variations = $product->getVariations();
                if ($variations->count() > 0) {

                    $error = new Intraface_Error;
                    $error->set('You cannot change the attached attribute groups when variations has been created');
                }
            } catch (Intraface_Gateway_Exception $e) {

            }
        }
        $groups = $group_gateway->findAll();

        $data = array('product' => $product, 'groups' => $groups);

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/productattributegroup.tpl.php');
        return $smarty->render($this, $data);

    }

}

