<?php
class Intraface_modules_procurement_Controller_PurchasePrice extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->getKernel()->module("procurement");
        $product_module = $this->getKernel()->useModule('product');

        $procurement = $this->context->getProcurement();

        $data = array(
            'items' => $procurement->getItems(),
            'procurement' => $procurement
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/purchaseprice');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->module("procurement");
        $product_module = $this->getKernel()->useModule('product');

        $procurement = $this->context->getProcurement();

        foreach ($_POST['items'] as $item) {
            $procurement->loadItem($item['id']);
            $procurement->item->setPurchasePrice($item['price']);
            $procurement->error->merge($procurement->item->error->getMessage());
        }

        if (!$procurement->error->isError()) {
            return new k_SeeOther($this->context->url());
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
