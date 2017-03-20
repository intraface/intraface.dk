<?php
class Intraface_modules_product_Controller_Show_Variations extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if (!$this->context->getProduct()->hasVariation()) {
            return new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'stock') {
            return 'Intraface_modules_stock_Controller_Variations';
        } elseif ($name == 'select_attribute_groups') {
            // @todo check whether product has attributes
            return 'Intraface_modules_product_Controller_Show_Variations_SelectAttributeGroups';
        } elseif (is_numeric($name)) {
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
        /*if (count($groups) == 0) {
         return new k_TemporaryRedirect($this->url('select_attribute_groups'));
         }*/

        $existing_groups = array();
        // $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $data = array(
            'product' => $product,
            'existing_groups' => $existing_groups,
            'groups' => $groups);

        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/variations');
        return $tpl->render($this, $data);
    }

    function renderHtmlEdit()
    {
        $product = $this->context->getProduct();
        $groups = $product->getAttributeGroups();
        /*if (count($groups) == 0) {
         return new k_TemporaryRedirect($this->url('select_attribute_groups'));
         }*/

        $existing_groups = array();
        // $group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $data = array(
            'product' => $product,
            'existing_groups' => $existing_groups,
            'groups' => $groups);

        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/variations-edit');
        return $tpl->render($this, $data);
    }

    function getProduct()
    {
        return $this->context->getProduct();
    }

    function postForm()
    {
        $module = $this->getKernel()->module('product');
        $product = $this->getProduct();
        if ($this->body('save') || $this->body('save_and_close')) {
            if (is_array($this->body('variation'))) {
                foreach ($this->body('variation') as $variation_data) {
                    if (isset($variation_data['used'])) {
                        if (!empty($variation_data['id'])) {
                            // update existing
                            $variation = $product->getVariation($variation_data['id']);
                        } else {
                            $variation = $product->getVariation();
                            $variation->product_id = $this->getProduct()->getId();
                            $variation->setAttributesFromArray($variation_data['attributes']);
                            $variation->save();
                        }

                        $detail = $variation->getDetail();
                        $detail->price_difference = 0; // Can be reimplemented: intval($variation_data['price_difference']);
                        $detail->weight_difference = intval($variation_data['weight_difference']);
                        $detail->save();
                    } elseif (!empty($variation_data['id'])) {
                        $variation = $product->getVariation($variation_data['id']);
                        $variation->delete();
                    }
                }
            }

            if ($this->body('save_and_close')) {
                return new k_SeeOther($this->url('../'));
            }

            return new k_SeeOther($this->url());
        }
        
        return new k_SeeOther($this->url(null, array($this->subview(), 'flare' => 'An error occurred. Probably too much data in the POST request. Solve it by having fewer variations.')));
    }

    function getProductId()
    {
        return $this->context->name();
    }
}
