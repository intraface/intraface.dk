<?php
class Intraface_modules_product_Controller_Related extends k_Component
{
    function postForm()
    {
        foreach ($_POST['product'] as $key=>$value) {
            $product = $this->getProduct();
            if (!empty($_POST['relate'][$key]) AND $product->setRelatedProduct($_POST['product'][$key], $_POST['relate'][$key])) {
            }
        }
        if (!empty($_POST['close'])) {
            return new k_SeeOther($this->url('../'));
        }
        return new k_SeeOther($this->url());

    }

    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $kernel->module('product');
        $smarty = new k_Template(dirname(__FILE__) . '/tpl/related.tpl.php');
        return $smarty->render($this);
    }
    function t($phrase)
    {
        return $phrase;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProducts()
    {
        $product = $this->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() AS $related) {
            $related_product_ids[] = $related['id'];
        }

        $keywords = $product->getKeywordAppender();

        if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {

            if (isset($_GET["search"])) {
                $product->getDBQuery()->setFilter("search", $_GET["search"]);
            }

            if (isset($_GET["keyword_id"])) {
                $product->getDBQuery()->setKeyword($_GET["keyword_id"]);
            }
        } else {
            $product->getDBQuery()->useCharacter();
        }

        $product->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $product->getDBQuery()->setCondition("product.id != " . $this->context->name());
        $product->getDBQuery()->setExtraUri("&amp;id=".(int)$this->context->name());
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "related_products", "sublevel");

        $list = $product->getList();

        return $list;
    }

    function getKeywords()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() AS $related) {
            $related_product_ids[] = $related['id'];
        }

        return $keywords = $product->getKeywordAppender();
    }

    function getRelatedProductIds()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() AS $related) {
            $related_product_ids[] = $related['id'];
        }
        return $related_product_ids;
    }

    function getProduct()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() AS $related) {
            $related_product_ids[] = $related['id'];
        }

        $keywords = $product->getKeywordAppender();

        if (isset($_GET["search"]) || isset($_GET["keyword_id"])) {

            if (isset($_GET["search"])) {
                $product->getDBQuery()->setFilter("search", $_GET["search"]);
            }

            if (isset($_GET["keyword_id"])) {
                $product->getDBQuery()->setKeyword($_GET["keyword_id"]);
            }
        } else {
            $product->getDBQuery()->useCharacter();
        }

        $product->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $product->getDBQuery()->setCondition("product.id != " . $this->context->name());
        $product->getDBQuery()->setExtraUri("&amp;id=".(int)$this->context->name());
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "related_products", "sublevel");

        return $product;
    }

    function renderHtmlDelete()
    {
        $product = $this->getProduct();
        $product->deleteRelatedProduct($_GET['del_related']);
        return new k_SeeOther($this->url('../'));
    }

}