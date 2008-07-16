<?php
class Intraface_modules_shop_Controller_Categories extends k_Controller
{

    function getShopId()
    {
        return $this->context->getShopId();
    }

    function GET()
    {
        $kernel = $this->registry->get('kernel');
        $webshop_module = $kernel->module('shop');
        $translation = $kernel->getTranslation('shop');
        $db = $this->registry->get('db');
        
        $redirect = Intraface_Redirect::factory($kernel, 'receive');

        $shop = $this->registry->get('category_gateway')->findById($this->context->name);
        
        $this->document->title = $translation->get('Categories for shop').' '.$shop->getName();
        $this->document->options = array($this->url('../') => $translation->get('Close', 'common'), $this->url('create') => $translation->get('Add new category'));
        
        $category = $this->getModel();
        $data['categories'] = $category->getAllCategories();
        
        if(isset($this->GET['product_id'])) {
            $data['product_id'] = $this->GET['product_id'];
        }
        
        return $this->render('Intraface/modules/shop/Controller/tpl/categories.tpl.php', $data);
    }
    
    function getModel($id = 0)
    {
        // @todo - cannot find the categories when using this one
        $db = $this->registry->get('db');
        $kernel = $this->registry->get('kernel');
        $shop = $this->registry->get('category_gateway')->findById($this->context->name);
        return new Intraface_Category($kernel, $db, new Intraface_Category_Type('shop', $shop->getId()), $id);
    }

    function _getModel()
    {
        return new Ilib_Category($this->registry->get('db'), 
            new Intraface_Category_Type('shop', $this->getShopId()));
    }

    function POST()
    {
        $kernel = $this->registry->get('kernel');
        $webshop_module = $kernel->module('shop');
        $translation = $kernel->getTranslation('shop');
        $db = $this->registry->get('db');
        
        $redirect = Intraface_Redirect::factory($kernel, 'receive');
        
        $shop = $this->registry->get('category_gateway')->findById($this->context->name);
        $category = new Intraface_Category($kernel, $db, new Intraface_Category_Type('shop', $shop->getId()));
        $appender = $category->getAppender($this->POST['product_id']);
        foreach($this->POST['category'] AS $category) {
            $category = new Intraface_Category($kernel, $db, new Intraface_Category_Type('shop', $shop->getId()), $category);
            $appender->add($category);
        }
        
        throw new k_http_Redirect($redirect->getRedirect($this->url('../')));
    }
    
    function forward($name) 
    {
        if ($name == 'create') {
            $next = new Intraface_modules_shop_Controller_Categories_Edit($this, $name);
            return $next->handleRequest();
        } elseif (is_numeric($name)) {
            $next = new Intraface_modules_shop_Controller_Categories_Show($this, $name);
            return $next->handleRequest();
        }
        
        throw new Exception('Unknown forward');
    }
}