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

        $shop = $this->registry->get('category_gateway')->findById($this->context->name);

        $this->document->title = $translation->get('Categories for shop'.' '.$shop->getName());
        $this->document->options = array($this->url('../') => $translation->get('Close', 'common'), $this->url('create') => $translation->get('Add new category'));
        
        $category = new Intraface_Category($kernel, $db, new Intraface_Category_Type('shop', $shop->getId()));
        $categories = $category->getAllCategories();
        
        return $this->render('Intraface/modules/shop/Controller/tpl/categories.tpl.php', array('categories' => $categories));
    }

    function POST()
    {
        $db = $this->registry->get('db');
        $kernel = $this->registry->get('kernel');
        $doctrine = $this->registry->get('doctrine');
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name);
        $featured = new Intraface_modules_shop_FeaturedProducts($kernel->intranet, $shop, $db);
        if ($featured->add($this->POST['headline'], new Keyword(new Product($this->registry->get('kernel')), $this->POST['keyword_id']))) {
            throw new k_http_Redirect($this->url());
        }
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