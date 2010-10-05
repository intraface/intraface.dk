<?php
class Intraface_modules_shop_Controller_Categories extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2, k_TemplateFactory $template)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_shop_Controller_Categories_Create';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_shop_Controller_Categories_Show';
        }
    }

    function renderHtml()
    {
        $webshop_module = $this->getKernel()->module('shop');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $shop_gateway = $this->getShopGateway();
        $shop = $shop_gateway->findById($this->context->name());

        $this->document->setTitle('Categories for shop' . $shop->getName());
        $this->document->options = array(
            $redirect->getRedirect($this->url('../')) => 'Close', $this->url('create') => 'Add new category'
        );

        $category = $this->getModel();
        $data['categories'] = $category->getAllCategories();
        $data['product_id'] = $this->query('product_id');

        if ($this->query('product_id')) {
            $data['product_id'] = $this->query('product_id');
        }

        $tpl = $this->template->create('Intraface/modules/shop/Controller/tpl/categories');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $webshop_module = $this->getKernel()->module('shop');

        $shop = $this->getShopGateway()->findById($this->context->name());
        $category = new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $shop->getId()));

        if ($this->body('append_product')) {
            // Append category to product
            $appender = $category->getAppender($this->body('product_id'));
            foreach ($this->body('category') AS $category) {
                $category = new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $shop->getId()), $category);
                $appender->add($category);
            }
            //$redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
            return new k_SeeOther($this->url('../../../'));
        } elseif ($this->body('action') == 'delete') {
            // delete category
            if(is_array($this->body('category'))) {
                foreach ($this->body('category') AS $category) {
                    $category = new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $shop->getId()), $category);
                    $category->delete();
                }
            }

            return $this->render();
        }

        throw new Exception('Invalid action');

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getShopId()
    {
        if ($this->query('shop_id')) {
            return intval($this->query('shop_id'));
        }
        return $this->context->getShopId();
    }

    function getShopGateway()
    {
        return $category_gateway = new Intraface_modules_shop_Shop_Gateway();
    }

    function getModel($id = 0)
    {
        return new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $this->getShopId()), $id);
    }

    /*
    function getModel()
    {
        return new Ilib_Category($this->mdb2,
            new Intraface_Category_Type('shop', $this->getShopId()));
    }
    */
}