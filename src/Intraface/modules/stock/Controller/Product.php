<?php
/**
 * @todo Check whether product is stock product - probably in dispatch
 * @author lsolesen
 */
class Intraface_modules_stock_Controller_Product extends k_Component
{
    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/product.tpl.php');
        return $smarty->render($this);
    }

    function getProduct()
    {
        $module = $this->getKernel()->module("product");
        return new Product($this->getKernel(), $this->context->name());
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getVariation()
    {
        $product_object = $this->getProduct();
        if (!empty($_GET['product_variation_id'])) {
            $variation = $product_object->getVariation($_GET['product_variation_id']);
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
        $product_object = $this->context->getProduct();

        if (!empty($_POST['product_variation_id'])) {
            $variation = $product_object->getVariation(intval($_POST['product_variation_id']));
            if (!$variation->getId()) {
                throw new Exception('Invalid variation. '.intval($_POST['product_variation_id']));
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

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}