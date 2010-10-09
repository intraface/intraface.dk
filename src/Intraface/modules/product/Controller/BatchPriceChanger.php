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
        $product->getDBQuery()->storeResult("use_stored", "products", "toplevel");
        $products = $product->getList();

        $data = array('products' => $products, 'product' => $product);

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/batchpricechanger');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module = $this->context->getKernel()->module("product");

        foreach ($this->body('product_id') AS $key => $product_id) {
            $product = new Product($this->context->getKernel(), $product_id);
            if ($this->body('change_type') == 'percent') {
                $new_price = $product->get('price') + intval($product->get('price') * ($_POST['price_change'] / 100));
                if ($this->body('round_off') == 'yes') {
                    $new_price = round($new_price, 0);
                }
                if ($product->save(array(
            		'price' => $new_price,
                    'before_price' => (float)$product->get('price')
                ))) {
                }
            } elseif ($this->body('change_type') == 'amount') {
                $new_price = $product->get('price') + intval($_POST['price_change']);
                if ($this->body('round_off') == 'yes') {
                    $new_price = round($new_price, 0);
                }
                if ($product->save(array(
            		'price' => $new_price,
                    'before_price' => (float)$product->get('price')
                ))) {
                }
            } elseif ($this->body('change_type') == 'fixed_amount') {
                $new_price = intval($_POST['price_change']);
                if ($this->body('round_off') == 'yes') {
                    $new_price = round($new_price, 0);
                }
                if ($product->save(array(
            		'price' => $new_price,
                    'before_price' => (float)$product->get('price')
                ))) {
                }
            } else {
                throw new Exception('Invalid change type');
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
