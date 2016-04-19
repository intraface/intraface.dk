<?php
class Intraface_modules_product_Controller_Productattributegroup extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');

        $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();
        $product = $this->getProduct();

        $existing_groups = array();
        foreach ($product->getAttributeGroups() as $group) {
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

        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/productattributegroup');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $product = $this->getProduct();

        if ($this->body('select')) {
            $existing_groups = array();
            $new_groups = array();
            foreach ($product->getAttributeGroups() as $group) {
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

            if (!isset($error) || $error->isError() == 0) {
                if (is_array($this->body('selected'))) {
                    $new_groups = $this->body('selected');
                }

                foreach (array_diff($existing_groups, $new_groups) as $id) {
                    $product->removeAttributeGroup($id);
                }

                foreach ($new_groups as $id) {
                    $product->setAttributeGroup($id);
                }

                $existing_groups = $new_groups;

                return new k_SeeOther('product_variations_edit.php?id='.$product->getId());
            }
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProduct()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        return new Product($this->getKernel(), $this->context->name());
    }
}
