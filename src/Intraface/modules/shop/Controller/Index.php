<?php
class Intraface_modules_shop_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
         $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Shops');
        $this->document->options = array($this->url('create') => 'Create');

        $shops = Doctrine::getTable('Intraface_modules_shop_Shop')->findByIntranetId($this->getKernel()->intranet->getId());

        if (count($shops) == 0) {
            $tpl = $this->template->create(dirname(__FILE__) . '/tpl/empty-table');
            return $tpl->render($this, array('message' => 'No shops has been created yet.'));
        }

        $data = array('shops' => $shops);
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/shops');
        return $tpl->render($this, $data);
    }

    function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_shop_Controller_Edit';
        }
        
        /**
         * Not finished. Can be removed if no costumers no longer interested 
        if ($name == 'discount-campaigns') {
            return 'Intraface_modules_shop_Controller_DiscountCampaigns';
        } */
        
        return 'Intraface_modules_shop_Controller_Show';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create(dirname(__FILE__) . '/tpl/content');
        return $tpl->render($this, array('content' => $content));
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function document()
    {
        return $this->document;
    }
}