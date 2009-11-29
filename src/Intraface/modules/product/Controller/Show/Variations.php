<?php
class Intraface_modules_product_Controller_Show_Variations extends k_Component
{
    function map($name)
    {
        if ($name == 'select_attribute_groups') {
            // @todo check whether product has attributes
            return 'Intraface_modules_product_Controller_Show_Variations_SelectAttributeGroups';
        } else if (is_numeric($name)) {
            return 'Intraface_modules_product_Controller_Variation';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $product = $this->context->getProduct();
        $groups = $product->getAttributeGroups();
        /*if(count($groups) == 0) {
         return new k_TemporaryRedirect($this->url('select_attribute_groups'));
         }*/

        $existing_groups = array();
        // $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();
        
        $data = array(
            'product' => $product,
            'existing_groups' => $existing_groups,
            'groups' => $groups);
        
        $tpl = new k_Template(dirname(__FILE__) . '/../tpl/variations-edit.tpl.php');
        return $tpl->render($this, $data);

    }

    function getProduct()
    {
        return $this->context->getProduct();
    }

    function postForm()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');

        $product = $this->getProduct();

        if (!empty($_POST['save']) || !empty($_POST['save_and_close'])) {

            if(isset($_POST['variation']) && is_array($_POST['variation'])) {
                foreach ($_POST['variation'] AS $variation_data) {

                    if (isset($variation_data['used'])) {
                        if (!empty($variation_data['id'])) {
                            // update existing
                            $variation = $product->getVariation($variation_data['id']);

                        } else {
                            $variation = $product->getVariation();
                            $variation->product_id = $_POST['id'];
                            $variation->setAttributesFromArray($variation_data['attributes']);
                            $variation->save();

                        }

                        $detail = $variation->getDetail();
                        $detail->price_difference = 0; /* Can be reimplemented: intval($variation_data['price_difference']); */
                        $detail->weight_difference = intval($variation_data['weight_difference']);
                        $detail->save();

                    } elseif (!empty($variation_data['id'])) {
                        $variation = $product->getVariation($variation_data['id']);
                        $variation->delete();
                    }
                }
            }

            if (!empty($_POST['save_and_close'])) {
                return new k_SeeOther($this->url('../'));
            }

            return new k_SeeOther($this->url());
        }

    }

    function getProductId()
    {
        return $this->context->name();
    }
}
