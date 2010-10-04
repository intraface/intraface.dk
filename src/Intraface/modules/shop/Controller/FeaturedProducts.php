<?php
require_once 'Ilib/Keyword.php';
require_once 'Intraface/shared/keyword/Keyword.php';
require_once 'Intraface/modules/product/Product.php';
require_once 'Intraface/modules/webshop/FeaturedProducts.php';

class Intraface_modules_shop_Controller_FeaturedProducts extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function renderHtml()
    {
        $webshop_module = $this->getKernel()->module('shop');

        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name());

        if (is_numeric($this->query('delete'))) {
            $featured = new Intraface_modules_shop_FeaturedProducts($this->getKernel()->intranet, $shop, $this->mdb2);
            if ($featured->delete($this->query('delete'))) {
                return new k_SeeOther($this->url());
            }
        }

        $this->document->setTitle('Featured products');
        $this->document->options = array($this->url('../') => 'Close');

        $featured = new Intraface_modules_shop_FeaturedProducts($this->getKernel()->intranet, $shop, $this->mdb2);
        $all = $featured->getAll();

        $keyword_object = new Intraface_Keyword_Appender(new Product($this->getKernel()));
        $keywords = $keyword_object->getAllKeywords();

        $data = array(
            'all' => $all,
            'kernel' => $this->getKernel(),
            'keywords' => $keywords);

        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/featuredproducts');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->find($this->context->name());
        $featured = new Intraface_modules_shop_FeaturedProducts($this->getKernel()->intranet, $shop, $this->mdb2);
        $keyword = new Keyword(new Product($this->getKernel()), $this->body('keyword_id'));
        if ($featured->add($this->body('headline'), $keyword)) {
            return new k_SeeOther($this->url());
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}