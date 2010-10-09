<?php
class Intraface_modules_product_Controller_BatchEdit extends k_Component
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
        $product->getDBQuery()->defineCharacter("character", "detail_translation.name");
        $product->getDBQuery()->usePaging("paging");
        $product->getDBQuery()->storeResult("use_stored", "products", "toplevel");
        $products = $product->getList();

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/batchedit');
        return $tpl->render($this, array('products' => $products, 'product' => $product));
    }

    function postForm()
    {
        $module = $this->context->getKernel()->module("product");
        $translation = $this->context->getKernel()->getTranslation('product');
        foreach ($_POST['name'] AS $key=>$value) {
            $product = new Product($this->context->getKernel(), $key);
            if ($product->save(array(
            'number' => $product->get('number'),
            'name' => $_POST['name'][$key],
            'description' => $_POST['description'][$key],
            'unit' => $product->get('unit_key'),
            'vat' => $product->get('vat'),
            'price' => $_POST['price'][$key],
            'weight' => $product->get('weight'),
            'do_show' => $product->get('do_show'),
            'stock' => $product->get('stock'),
            'state_account_id' => $product->get('state_account_id')
            ))) {
                // 'quantity' => $_POST['quantity'][$key], gammelt lager - udg�r

                $string_appender = new Intraface_Keyword_StringAppender($product->getKeywords(), $product->getKeywordAppender());
                $string_appender->addKeywordsByString($_POST['keywords'][$key]);
            }
            echo $product->error->view();
        }
        return new k_SeeOther($this->url('../', array('use_stored' => 'true')));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
