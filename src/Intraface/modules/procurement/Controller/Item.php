<?php
class Intraface_modules_procurement_Controller_Item extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_product_Controller_Selectproduct';
    }

    function getProcurement()
    {
        return $this->context->getProcurement();
    }

    function postForm()
    {
        $procurement_module = $this->getKernel()->module("procurement");
        $product_module = $this->getKernel()->useModule('product');

        $procurement = $this->getProcurement();
        $procurement->loadItem(intval($this->name()));

        if ($id = $procurement->item->save($_POST)) {
            return new k_SeeOther($this->context->context->url());
        } else {
            $values = $_POST;
        }

        return $this->render();
    }

    /**
     * Used to change a product
     *
     * @see Product_Controller_SelectProduct
     *
     * @param $product
     * @param $quantity
     *
     * @return boolean
     */
    function addItem($product, $quantity)
    {
        $this->getProcurement()->loadItem(intval($this->name()));
        $this->getProcurement()->item->changeProduct($product['product_id'], $product['product_variation_id']);
        return true;
    }

    function renderHtml()
    {
        $procurement_module = $this->getKernel()->module("procurement");
        $product_module = $this->getKernel()->useModule('product');
        $translation = $this->getKernel()->getTranslation('procurement');

        $procurement = new Procurement($this->getKernel(), intval($this->context->context->name()));
        $procurement->loadItem(intval($this->name()));
        $values['quantity'] = $procurement->item->get('quantity');
        $values['dk_unit_purchase_price'] = $procurement->item->get('dk_unit_purchase_price');


        if (isset($_GET['change_product'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('selectproduct'), NET_SCHEME . NET_HOST . $this->url());
            $redirect->askParameter('product_id');
            return new k_SeeOther($url);
        }

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $returned_values = unserialize($redirect->getParameter('product_id'));
            $procurement->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
            $procurement->loadItem(intval($_GET["id"]));
        }

        $data = array('procurement' => $procurement, 'values' => $values);

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/item-edit');
        return $tpl->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
