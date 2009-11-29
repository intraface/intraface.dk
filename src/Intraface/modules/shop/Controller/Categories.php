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

    function getShopId()
    {
        return $this->context->getShopId();
    }

    function renderHtml()
    {
        $webshop_module = $this->getKernel()->module('shop');
        $translation = $this->getKernel()->getTranslation('shop');
        $db = $this->mdb2;

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $category_gateway = $this->getCategoryGateway();
        $shop = $category_gateway->findById($this->context->name());

        $this->document->setTitle('Categories for shop' . $shop->getName());
        $this->document->options = array(
            $redirect->getRedirect($this->url('../')) => 'Close', $this->url('create') => 'Add new category'
        );

        $category = $this->getModel();
        $data['categories'] = $category->getAllCategories();

        if (isset($this->GET['product_id'])) {
            $data['product_id'] = $this->GET['product_id'];
        }

        $tpl = $this->template->create('Intraface/modules/shop/Controller/tpl/categories');
        return $tpl->render($this, $data);
    }

    function getCategoryGateway()
    {
        return $category_gateway = new Intraface_modules_shop_Shop_Gateway();
    }

    function _getModel($id = 0)
    {
        // @todo - cannot find the categories when using this one
        return new Intraface_Category($this->getKernel(), $this->mdb2, new Intraface_Category_Type('shop', $shop->getId()), $id);
    }

    function getModel()
    {
        return new Ilib_Category($this->mdb2,
            new Intraface_Category_Type('shop', $this->getShopId()));
    }

    function postForm()
    {
        $webshop_module = $this->getKernel()->module('shop');
        $translation = $this->getKernel()->getTranslation('shop');
        $db = $this->mdb2;

        $shop = $this->getCategoryGateway()->findById($this->context->name());
        $category = new Intraface_Category($this->getKernel(), $db, new Intraface_Category_Type('shop', $shop->getId()));

        if($this->body('append_product')) {
            // Append category to product
            $appender = $category->getAppender($this->body('product_id'));
            foreach ($this->body('category') AS $category) {
                $category = new Intraface_Category($this->getKernel(), $db, new Intraface_Category_Type('shop', $shop->getId()), $category);
                $appender->add($category);
            }
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
            return new k_SeeOther($redirect->getRedirect($this->url('../')));
        } elseif ($this->body('action') == 'delete') {
            // delete category
            if(is_array($this->body('category'))) {
                foreach ($this->body('category') AS $category) {
                    $category = new Intraface_Category($this->getKernel(), $db, new Intraface_Category_Type('shop', $shop->getId()), $category);
                    $category->delete();
                }
            }

            return $this->render();
        }

        throw new exception('Invalid action');

    }

    function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_shop_Controller_Categories_Edit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_shop_Controller_Categories_Show';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}