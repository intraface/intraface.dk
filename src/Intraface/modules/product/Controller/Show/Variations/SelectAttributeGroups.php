<?php
class Intraface_modules_product_Controller_Show_Variations_SelectAttributeGroups extends Intraface_modules_product_Controller_AttributeGroups
{

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function checkForAddedVariations()
    {
        $product = $this->context->context->getProduct();
        $error = new Intraface_Error;
        $existing_groups = array();
        foreach ($product->getAttributeGroups() AS $group) {
            $existing_groups[] = $group['id'];
        }
        if (count($existing_groups) > 0) {
            try {
                $variations = $product->getVariations();
                if ($variations->count() > 0) {
                    $error->set('You cannot change the attached attribute groups when variations has been created');
                }
            } catch (Intraface_Gateway_Exception $e) {

            }
        }

        return $error;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());


        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $groups = $gateway->findAll();

        $data = array('groups' => $groups,
            'error' => $this->checkForAddedVariations()
        );

        $smarty = new k_Template('Intraface/modules/product/Controller/tpl/select-attribute-groups.tpl.php');
        return $smarty->render($this, $data);

    }

    function postForm()
    {
        $error = $this->checkForAddedVariations();

        if ($error->isError() == 0) {
            if (isset($_POST['selected']) && is_array($_POST['selected'])) {
                $new_groups = $_POST['selected'];
            }

            $product = $this->context->context->getProduct();
            $existing_groups = array();
            foreach ($product->getAttributeGroups() AS $group) $existing_groups[] = $group['id'];
            foreach (array_diff($existing_groups, $new_groups) AS $id) {
                $product->removeAttributeGroup($id);
            }

            foreach ($new_groups AS $id) {
                $product->setAttributeGroup($id);
            }

            return new k_SeeOther($this->url('..'));
        }
    }

}
?>