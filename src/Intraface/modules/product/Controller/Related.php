<?php
class Intraface_modules_product_Controller_Related extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $kernel->module('product');
        $smarty = $this->template->create(dirname(__FILE__) . '/tpl/related');
        return $smarty->render($this);
    }

    function postForm()
    {
        foreach ($this->body('product') as $key => $value) {
            $product = $this->getProduct();
            if (!empty($_POST['relate'][$key]) and $product->setRelatedProduct($_POST['product'][$key], $_POST['relate'][$key])) {
            }
        }
        if ($this->body('close')) {
            return new k_SeeOther($this->url('../'));
        }
        return new k_SeeOther($this->url());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getProducts()
    {
        $product = $this->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() as $related) {
            $related_product_ids[] = $related['id'];
        }

        $keywords = $product->getKeywordAppender();

        if ($this->query("search") || $this->query("keyword_id")) {
            if ($this->query("search")) {
                $product->getDBQuery()->setFilter("search", $this->query("search"));
            }

            if ($this->query("keyword_id")) {
                $product->getDBQuery()->setKeyword($this->query("keyword_id"));
            }
        } else {
            $product->getDBQuery()->useCharacter();
        }

        $product->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $product->getDBQuery()->setCondition("product.id != " . $this->context->name());
        $product->getDBQuery()->setExtraUri("&amp;id=".(int)$this->context->name());
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "related_products", "sublevel");
        $product->getDBQuery()->setUri($this->url('.'));

        $list = $product->getList();

        return $list;
    }

    function getKeywords()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() as $related) {
            $related_product_ids[] = $related['id'];
        }

        return $keywords = $product->getKeywordAppender();
    }

    function getRelatedProductIds()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() as $related) {
            $related_product_ids[] = $related['id'];
        }
        return $related_product_ids;
    }

    function getProduct()
    {
        $product = $this->context->getProduct();

        $related_product_ids = array();
        foreach ($product->getRelatedProducts() as $related) {
            $related_product_ids[] = $related['id'];
        }

        $keywords = $product->getKeywordAppender();

        if ($this->query("search") || $this->query("keyword_id")) {
            if ($this->query("search")) {
                $product->getDBQuery()->setFilter("search", $this->query("search"));
            }

            if ($this->query("keyword_id")) {
                $product->getDBQuery()->setKeyword($this->query("keyword_id"));
            }
        } else {
            $product->getDBQuery()->useCharacter();
        }

        $product->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $product->getDBQuery()->setCondition("product.id != " . $this->context->name());
        $product->getDBQuery()->setExtraUri("&amp;id=".(int)$this->context->name());
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "related_products", "sublevel");
        $product->getDBQuery()->setUri($this->url('.'));

        return $product;
    }

    function renderHtmlDelete()
    {
        $product = $this->getProduct();
        $product->deleteRelatedProduct($this->query('del_related'));
        return new k_SeeOther($this->url('../'));
    }
}
