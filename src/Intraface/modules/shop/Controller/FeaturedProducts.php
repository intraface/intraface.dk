<?php
require_once 'Intraface/shared/keyword/Keyword.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/webshop/FeaturedProducts.php';

class Intraface_modules_shop_Controller_FeaturedProducts extends k_Controller
{
    function GET()
    {
        $kernel = $this->registry->get('kernel');
        $db = $this->registry->get('db');
        $doctrine = $this->registry->get('doctrine');
        $webshop_module = $kernel->module('shop');
        $translation = $kernel->getTranslation('shop');

        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name);

        if (!empty($this->GET['delete']) AND is_numeric($this->GET['delete'])) {
            $featured = new Intraface_modules_shop_FeaturedProducts($kernel->intranet, $shop, $db);
            if ($featured->delete($this->GET['delete'])) {
                throw new k_http_Redirect($this->url());
            }
        }

        $this->document->title = 'Featured products';
        $this->document->options = array($this->url('../') => 'Close');

        $featured = new Intraface_modules_shop_FeaturedProducts($kernel->intranet, $shop, $db);
        $all = $featured->getAll();

        $keyword_object = new Intraface_Keyword_Appender(new Product($kernel));
        $keywords = $keyword_object->getAllKeywords();

        $data = array('all' => $all, 'kernel' => $kernel, 'keywords' => $keywords);

        return $this->render(dirname(__FILE__) . '/tpl/featuredproducts.tpl.php', $data);
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
}