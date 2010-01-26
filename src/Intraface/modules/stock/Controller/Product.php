<?php
/**
 * @todo Check whether product is stock product - probably in dispatch
 * @author lsolesen
 */
class Intraface_modules_stock_Controller_Product extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/product');
        return $smarty->render($this);
    }

    function getProduct()
    {
        return $this->context->getProduct();
    }

    function getVariation()
    {
        $product_object = $this->getProduct();
        if ($product_object->hasVariation()) {
            $variation = $product_object->getVariation($this->context->name());
        } else {
            $variation = NULL;
        }

        return $variation;
    }

    function getValues()
    {
        return array();
    }

    function postForm()
    {
        $product_object = $this->getProduct();

        if ($product_object->hasVariation()) {
            $variation = $this->getVariation();
            if (!$variation->getId()) {
                throw new Exception('Invalid variation.');
            }
            if ($variation->getStock($product_object)->regulate($_POST)) {
                return new k_SeeOther($this->url('../'));
            }
        } else {
            if ($product_object->getStock()->regulate($_POST)) {
                return new k_SeeOther($this->url('../'));
            }
        }

        $values = $_POST;

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}