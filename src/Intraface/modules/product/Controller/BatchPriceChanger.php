<?php
class Intraface_modules_product_Controller_BatchPriceChanger extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->context->getKernel()->module("product");

        $product = new Product($this->context->getKernel());
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "products", "toplevel");
        $products = $product->getList();

        $data = array('products' => $products, 'product' => $product);

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/batchpricechanger');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->context->getKernel()->module("product");

        foreach ($_POST['product_id'] AS $key => $product_id) {
            $product = new Product($this->context->getKernel(), $product_id);
            if ($product->save(array(
            	'price' => $product->get('price') + intval($_POST['price_change'])
            ))) {
                // 'quantity' => $_POST['quantity'][$key], gammelt lager - udg�r
            }
            echo $product->error->view();
        }
        return new k_SeeOther($this->url('../', array('use_stored' => 'true', 'flare' => 'Prices have been changed')));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
